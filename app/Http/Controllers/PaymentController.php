<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Invoices;
use App\Models\Invoice_settings;
use App\Models\Activities;
use App\Models\Applications;
use App\Models\UserRoles;
use App\Services\TableFilterCountService;
use App\Services\TaxSummaryService;
use Auth;
use Mail;
use App\Mail\Invoicemail;
use App\Models\PaymentARs;
use DateTime;
use DataTables;
use DB;
use App\Models\Internal_Invoices;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected TaxSummaryService $taxSummaryService;

    public function __construct(TaxSummaryService $taxSummaryService)
    {
        $this->taxSummaryService = $taxSummaryService;
    }


    public function invoice_id()
    {
        $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $id = "";
        for($i=0; $i<10; $i++){
            $id = $id.$ch[rand(0, strlen($ch)-1)];
        }
        if(PaymentARs::where('invoice_no','=',$id)->first()){
            return invoice_id();
        }
        return $id;
    }

    // public function my_payments()
    // {
    //     $user = auth()->user();

    //     if ($user->user_type == "Subscriber") {
    //         $subscriber = $user;
    //     } else {
    //         $subscriber = User::find($user->added_by);
    //     }

    //     $paymentAR = PaymentARs::select(
    //             'client_id',
    //             'application_id',
    //             'service_description',
    //             'service_provider',
    //             'service_taken',
    //             'payment_mode',
    //             'amount',
    //             DB::raw('SUM(paid_amount) as paid_amount'),
    //             DB::raw('(SUM(amount) - SUM(paid_amount)) as outstanding'),
    //             DB::raw('MAX(created_at) as latest_payment_date')
    //         )
    //         ->where('subscriber_id', $subscriber->id)
    //         ->where('type', 'ar')
    //         ->groupBy('client_id', 'application_id', 'service_description', 'service_provider', 'service_taken', 'payment_mode', 'amount')
    //         ->orderBy('latest_payment_date', 'desc')
    //         ->get();

    //     $page = "payments";

    //     return view('web.payments', compact('user', 'page', 'paymentAR'));
    // }

    public function my_payments()
    {
        $user = auth()->user();

        $subscriber = $user->user_type == "Subscriber"
            ? $user
            : User::find($user->added_by);

        // get all payment rows (no grouping in SQL)
        $paymentAR = PaymentARs::with(['client', 'application'])
            ->where('subscriber_id', $subscriber->id)
            ->where('type', 'ar')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $this->annotatePaymentAmountDisplays($paymentAR, (int) $subscriber->id, 'ar');

        $paymentAR = $paymentAR->sortByDesc('created_at')->values();

        $page = "payments";
        $clients = $user->user_type === 'Subscriber'
            ? Clients::where('subscriber_id', $subscriber->id)->get()
            : Clients::where('user_id', $user->id)->get();
        $paymentModeFilters = TableFilterCountService::countBy(
            $paymentAR,
            fn ($payment) => $payment->payment_mode
        );

        return view('web.payments', compact('user', 'page', 'paymentAR', 'paymentModeFilters', 'clients'));
    }


    public function  payment_made()
    {
        $user = $this->check_login();

        // $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }

        $paymentAP = PaymentARs::where('subscriber_id', '=', $subscriber->id)
            ->where('type', 'ap')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $this->annotatePaymentAmountDisplays($paymentAP, (int) $subscriber->id, 'ap');

        $paymentAP = $paymentAP->sortByDesc('created_at')->values();

        $page = "payments";
        $clients = $user->user_type === 'Subscriber'
            ? Clients::where('subscriber_id', $subscriber->id)->get()
            : Clients::where('user_id', $user->id)->get();
        $paymentModeFilters = TableFilterCountService::countBy(
            $paymentAP,
            fn ($payment) => $payment->payment_mode
        );

        return view('web.payments_made', compact('user', 'page', 'paymentAP', 'paymentModeFilters', 'clients'));
    }

    /**
     * Annotate payment rows with 2-part Amount To Pay (opening/total on partials)
     * and running outstanding after each payment — same format for AR and AP.
     */
    protected function annotatePaymentAmountDisplays($payments, int $subscriberId, string $type): void
    {
        if ($payments->isEmpty()) {
            return;
        }

        $invoiceTotals = Internal_Invoices::where('subscriber_id', $subscriberId)
            ->where('type', $type)
            ->whereIn('invoice_no', $payments->pluck('invoice_no')->filter()->unique()->values()->all())
            ->pluck('total', 'invoice_no');

        $grouped = $payments->groupBy(function ($row) {
            return (string) ($row->invoice_no ?: implode('|', [
                $row->client_id,
                $row->application_id,
                $row->service_description,
                $row->amount,
            ]));
        });

        foreach ($grouped as $group) {
            $sorted = $group->values();
            $invoiceNo = optional($sorted->first())->invoice_no;
            $invoiceTotal = $invoiceNo && isset($invoiceTotals[$invoiceNo])
                ? (float) $invoiceTotals[$invoiceNo]
                : (float) ($sorted->max('amount') ?: optional($sorted->first())->amount);
            $runningPaid = 0.0;

            foreach ($sorted as $index => $item) {
                $openingBalance = max(0, $invoiceTotal - $runningPaid);
                $runningPaid += (float) $item->paid_amount;
                $item->invoice_total = round($invoiceTotal, 2);
                $item->opening_balance = round($openingBalance, 2);
                $item->amount_to_pay_display = $index === 0
                    ? number_format($invoiceTotal, 2, '.', '')
                    : number_format($openingBalance, 2, '.', '')
                        . '/'
                        . number_format($invoiceTotal, 2, '.', '');
                $item->cumulative_paid = round($runningPaid, 2);
                $item->outstanding_balance = round(max(0, $invoiceTotal - $runningPaid), 2);
            }
        }
    }
    public function check_login()
    {
        $user = Auth::user();
        if ($user) {
            return $user;
        } else {
            $user = auth()->guard('affiliates')->user();
            if ($user) {
                $user = User::where('email', $user->email)->first();
                $user['type_user'] = 'affiliate';
                return $user;
            }
            return redirect()->route('login');
        }
    }
    public function add_ar_payments(){

        $user = auth()->user();

        if ($user) {
            if ($redirect = \App\Support\NoClientGuard::redirectIfNoClients($user)) {
                return $redirect;
            }

            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }

            $page = "payments";
            $users = User::where('user_type','Subscriber')->get();
            $payments = Invoices::orderBy('created_at', 'desc')->get();

            $invoices = $this->buildOutstandingInvoices($subscriber->id, 'ar');

            // Only clients that appear on outstanding AR invoices (and belong to this user when staff)
            $clientIds = $invoices->pluck('client_id')->filter()->unique()->values()->all();
            if ($user->user_type != "Subscriber") {
                $allowedClientIds = Clients::where('subscriber_id', $subscriber->id)
                    ->where('user_id', $user->id)
                    ->whereIn('id', $clientIds ?: [0])
                    ->pluck('id')
                    ->all();
                $invoices = $invoices->filter(function ($invoice) use ($allowedClientIds) {
                    return in_array($invoice['client_id'] ?? null, $allowedClientIds, true);
                })->values();
                $clientIds = $allowedClientIds;
            }
            $clients = empty($clientIds)
                ? collect()
                : Clients::whereIn('id', $clientIds)->orderBy('name')->get();

            return view('web.add_ar_payments', compact('user', 'payments', 'page','subscriber','clients', 'invoices'));
        } else {
            return back();
        }
    }
    
    public function getInvoiceDetails($id)
    {   
        $subscriberId = auth()->user()->added_by ? auth()->user()->added_by : auth()->user()->id;
        $invoice = Internal_Invoices::with('items')->where('subscriber_id', $subscriberId)
            ->where('type', 'ar')
            ->where('invoice_no', $id)
            ->first();

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $client = $this->resolveInvoiceClient($invoice, $subscriberId);
        $application = $this->resolveInvoiceApplication($invoice, $client, $subscriberId);

        $paidRows = PaymentARs::where('subscriber_id', $subscriberId)
            ->where('type', 'ar')
            ->where('invoice_no', $invoice->invoice_no)
            ->get();

        $paidAmount = round((float) $paidRows->sum('paid_amount'), 2);
        $amount = round((float) $invoice->total, 2);
        $outstanding = round(max(0, $amount - $paidAmount), 2);

        $applicationCode = $application ? (string) $application->application_id : null;
        $applicationName = $application
            ? trim((string) ($application->application_name ?? ''))
            : null;
        $applicationDisplay = $applicationName !== '' && $applicationName !== null
            ? $applicationName . ' (' . $applicationCode . ')'
            : ($applicationCode ?: null);

        return response()->json([
            'success' => 'Successfull', 
            'client' => optional($client)->id,
            'applicationID' => $applicationCode,
            'applicationName' => $applicationName,
            'applicationDisplay' => $applicationDisplay,
            'service' => $invoice->detail,
            'amount' => $amount, 
            'paidAmmount' => $paidAmount,
            'outstandingAmount' => $outstanding,
        ], 200);
    }

    public function getAPInvoiceDetails($id){
        $user = Auth::user();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        } else {
            $subscriber = User::find($user->added_by);
            $clients = Clients::where('user_id', '=', $user->id)->get();
        }
        $invoice = Internal_Invoices::where('subscriber_id', $subscriber->id)
            ->where('type', 'ap')
            ->where('invoice_no', $id)
            ->first();

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $client = Clients::where('subscriber_id', $subscriber->id)
            ->where(function ($query) use ($invoice) {
                $query->where('email', $invoice->to_email)
                    ->orWhere('name', $invoice->to_name);
            })
            ->first();
        $serviceProvider = $invoice->to_name;
        $serviceTaken = $invoice->detail;
        $amount = (float) $invoice->total;

        $paidRows = PaymentARs::where('subscriber_id', $subscriber->id)
            ->where('type', 'ap')
            ->where('invoice_no', $invoice->invoice_no)
            ->get();

        $paidAmount = (float) $paidRows->sum('paid_amount');
        $outstanding = max(0, ((float) $amount) - $paidAmount);

        return response()->json([
            'success' => 'Successfull', 
            'client' => optional($client)->id,
            'serviceProvider' => $serviceProvider,
            'serviceTaken' => $serviceTaken,
            'amount' => $amount, 
            'paidAmmount' => $paidAmount,
            'outstandingAmount' => $outstanding,
        ], 200);
    }

    public function add_ap_payments(){
        $user = Auth::user();
        if ($user) {
            if ($redirect = \App\Support\NoClientGuard::redirectIfNoClients($user)) {
                return $redirect;
            }

            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            } else {
                $subscriber = User::find($user->added_by);
                $clients = Clients::where('user_id', '=', $user->id)->get();
            }
            $page = "payments";
            $users = User::where('user_type','Subscriber')->get();
            $payments = Invoices::orderBy('created_at', 'desc')->get();

            $invoices = $this->buildOutstandingInvoices($subscriber->id, 'ap');
            
            // $payments = Invoices::where('type', 'inward')->orderBy('created_at', 'desc')->get();
            return view('web.add_ap_payments', compact('user', 'payments', 'page','subscriber','clients', 'invoices'));
        } else {
            return back();
        }
    }

    public function payment_received(Request $request){
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date_format:d-m-Y',
            'client_id' => 'required|exists:clients,id',
        ]);

        $paymentDate = Carbon::createFromFormat('d-m-Y', $request->payment_date)->startOfDay();
        if ($paymentDate->gt(Carbon::today())) {
            return back()->withInput()->withErrors([
                'payment_date' => 'Payment date cannot be a future date.',
            ]);
        }
        $application = null;
        if (!empty($request->application_id)) {
            $application = Applications::where(function ($query) use ($request) {
                $query->where('application_id', $request->application_id);
                if (ctype_digit((string) $request->application_id)) {
                    $query->orWhere('id', (int) $request->application_id);
                }
            })->first();
        }
        $data = $request->except(['_token','application_id','local_time','payment_entry_type','invoices_list','outstanding_amount']);
        $subscriber = auth()->user()->added_by ? auth()->user()->added_by : auth()->user()->id;
        $selectedInvoice = null;
        $selectedInvoiceNo = null;
        $outstandingAmount = (float) ($request->amount ?? 0);
        if (!empty($request->invoices_list)) {
            $selectedInvoice = Internal_Invoices::with('items')->where('invoice_no', $request->invoices_list)
                ->where('subscriber_id', $subscriber)
                ->where('type', 'ar')
                ->first();
            if ($selectedInvoice) {
                $selectedInvoiceNo = $selectedInvoice->invoice_no;
                $paidAmount = (float) PaymentARs::where('subscriber_id', $subscriber)
                    ->where('type', 'ar')
                    ->where('invoice_no', $selectedInvoiceNo)
                    ->sum('paid_amount');
                $outstandingAmount = max(0, ((float) $selectedInvoice->total) - $paidAmount);
                $data['amount'] = (float) $selectedInvoice->total;
                if (empty($data['service_description'])) {
                    $data['service_description'] = $selectedInvoice->detail;
                }
                if (!$application) {
                    $client = Clients::find($request->client_id);
                    $application = $this->resolveInvoiceApplication($selectedInvoice, $client, $subscriber);
                }
            }
        }
        if ((float) $request->paid_amount > $outstandingAmount + 0.00001) {
            return back()->withInput()->withErrors([
                'paid_amount' => 'Total Paid amount should not exceed ' . number_format($outstandingAmount, 2, '.', '') . ' (Outstanding) !.',
            ]);
        }

        $data['invoice_no'] = $selectedInvoiceNo ?: $this->invoice_id();
        $data['subscriber_id'] = $subscriber;
        if($application){
            $data['application_id'] = $application->id;
        }

        $data['type'] ='ar';
        $data['payment_date'] = $paymentDate->format('Y-m-d');
        $paymentAR = PaymentARs::create($data);

        if ($selectedInvoiceNo) {
            $ops = app(\App\Services\OperationalNotificationService::class);
            if ($ops->invoiceIsFullyPaid((int) $subscriber, $selectedInvoiceNo, 'ar')) {
                $client = !empty($data['client_id']) ? Clients::find($data['client_id']) : null;
                $ops->notifyFullPaymentReceived(
                    (int) $subscriber,
                    $client,
                    $application ?: null,
                    $selectedInvoiceNo
                );
            }
        }

        $activity = new Activities();
        $activity->subscriber_id = $subscriber ;
        $activity->user_id = auth()->user()->id;
        $activity->user_name =  auth()->user()->name;
        $activity->activity_name = "New AR Record added";
        if (auth()->user()->user_type == "Subscriber") {
            $activity->activity_detail = "New AR Record added by " .  auth()->user()->name . " at " . $request->local_time;
        } else {
            $activity->activity_detail = "New AR Record added by " .  auth()->user()->name . " at " . $request->local_time;
        }
        $activity->activity_icon = "invoice.jpg";
        $activity->local_time = $request->local_time;
        $activity->save();
        return redirect()->route('my_payments')->with('payments_received', 'AR (Payments Received) record created successfully.');
    }
// NEED TO FIX ENTRY FORM FOR PAYMENT _AP TYPE BECAUSE IT DOES NOT HAVE CLIENT 
    public function  advance_payment(Request $request){
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
        ]);
        $data = $request->except(['_token','local_time']);
        $subscriber = auth()->user()->added_by ? auth()->user()->added_by : auth()->user()->id;
        $selectedInvoice = null;
        $selectedInvoiceNo = null;
        $outstandingAmount = (float) ($request->amount ?? 0);
        if (!empty($request->invoices_list)) {
            $selectedInvoice = Internal_Invoices::where('invoice_no', $request->invoices_list)
                ->where('subscriber_id', $subscriber)
                ->where('type', 'ap')
                ->first();
            if ($selectedInvoice) {
                $selectedInvoiceNo = $selectedInvoice->invoice_no;
                $paidAmount = (float) PaymentARs::where('subscriber_id', $subscriber)
                    ->where('type', 'ap')
                    ->where('invoice_no', $selectedInvoiceNo)
                    ->sum('paid_amount');
                $outstandingAmount = max(0, ((float) $selectedInvoice->total) - $paidAmount);
            }
        }
        if ((float) $request->paid_amount > $outstandingAmount) {
            return back()->withInput()->withErrors([
                'paid_amount' => 'Total Paid amount should not exceed ' . number_format($outstandingAmount, 2, '.', '') . ' (Outstanding) !.',
            ]);
        }

        $data['invoice_no'] = $selectedInvoiceNo ?: $this->invoice_id();
        $data['subscriber_id'] = $subscriber;
        $data['type'] ='ap';
        $data['payment_date'] =now();
        $paymentAR = PaymentARs::create($data);

        $activity = new Activities();
        $activity->subscriber_id = $subscriber ;
        $activity->user_id = auth()->user()->id;
        $activity->user_name =  auth()->user()->name;
        $activity->activity_name = "New AP Record added";
        if (auth()->user()->user_type == "Subscriber") {
            $activity->activity_detail = "New AP Record added by " .  auth()->user()->name . " at " . $request->local_time;
        } else {
            $activity->activity_detail = "New AP Record added by " .  auth()->user()->name . "(" . auth()->user()->$subscriber->name . ") at " . $request->local_time;
        }
        $activity->activity_icon = "invoice.jpg";
        $activity->local_time = $request->local_time;
        $activity->save();
        return redirect()->route('payment_made')->with('advance_payment', 'AP (Payments Made) record created successfully.');
    }
    public function subscriberPayments()
    {
        $user = auth()->user();

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
         $paymentARs = PaymentARs::where('subscriber_id', '=', $subscriber->id)->orderBy('created_at', 'desc')->get();
         return DataTables::of($paymentARs)
                ->addIndexColumn()
                ->editColumn('client', function ($row) {

                    return $row->client_id ? $row->client->name.'('.$row->client_id.')' :'';
                })

                ->editColumn('outstanding', function ($row) {
                    return ($row->amount - $row->paid_amount);
                })
                ->editColumn('payment_date', function ($row) {
                    return date("d-m-Y", strtotime($row->payment_date));
                })
                ->editColumn('payment_type', function ($row) {
                    return $row->type == 'ap' ? 'AP' :'AR';
                })
                ->editColumn('created_at', function ($row) {
                    return date("d-m-Y H:i:s", strtotime($row->created_at));
                })

                ->make(true);
    }

    private function buildOutstandingInvoices($subscriberId, $type)
    {
        $invoiceRows = Internal_Invoices::with('items')->where('subscriber_id', $subscriberId)
            ->where('type', $type)
            ->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'withdrawn'])
            ->orderBy('created_at', 'asc')
            ->get();

        $payments = PaymentARs::where('subscriber_id', $subscriberId)
            ->where('type', $type)
            ->orderBy('created_at', 'asc')
            ->get();

        $paymentsByInvoice = $payments->groupBy('invoice_no');

        return $invoiceRows
            ->map(function ($invoice) use ($type, $paymentsByInvoice, $subscriberId) {
                $group = $paymentsByInvoice->get($invoice->invoice_no, collect());
                $totalAmount = round((float) $invoice->total, 2);
                $totalPaid = round((float) $group->sum('paid_amount'), 2);
                $outstanding = round(max(0, $totalAmount - $totalPaid), 2);

                if ($outstanding <= 0) {
                    return null;
                }

                $client = $this->resolveInvoiceClient($invoice, $subscriberId);
                $application = $type === 'ar'
                    ? $this->resolveInvoiceApplication($invoice, $client, $subscriberId)
                    : null;

                $clientName = optional($client)->name ?? ($invoice->to_name ?: 'N/A');
                $serviceDescription = $invoice->detail ?: 'N/A';
                $applicationCode = $application ? (string) $application->application_id : null;
                $applicationName = $application ? trim((string) ($application->application_name ?? '')) : '';
                $applicationDisplay = $applicationName !== ''
                    ? $applicationName . ' (' . $applicationCode . ')'
                    : ($applicationCode ?: null);

                $applicationId = $application
                    ? $application->id
                    : optional($group->first())->application_id;
                $serviceProvider = $type === 'ap' ? ($invoice->to_name ?: 'N/A') : optional($group->first())->service_provider;
                $serviceTaken = $type === 'ap' ? ($invoice->detail ?: 'N/A') : optional($group->first())->service_taken;

                $displayLabel = $type === 'ap'
                    ? sprintf('%s - %s - %s', $invoice->invoice_no, $invoice->to_name ?: 'N/A', $serviceDescription)
                    : sprintf(
                        '%s - %s%s - %s',
                        $invoice->invoice_no,
                        $clientName,
                        optional($client)->id ? ' (' . $client->id . ')' : '',
                        $applicationDisplay ?: $serviceDescription
                    );

                return [
                    'id' => $invoice->invoice_no,
                    'client_id' => optional($client)->id,
                    'application_id' => $applicationId,
                    'application_code' => $applicationCode,
                    'application_display' => $applicationDisplay,
                    'service_description' => $invoice->detail,
                    'service_provider' => $serviceProvider,
                    'service_taken' => $serviceTaken,
                    'amount' => $totalAmount,
                    'paid_amount' => $totalPaid,
                    'outstanding_amount' => $outstanding,
                    'display_label' => $displayLabel,
                ];
            })
            ->filter()
            ->values();
    }

    private function resolveInvoiceClient(Internal_Invoices $invoice, $subscriberId): ?Clients
    {
        if (!empty($invoice->to_email)) {
            $byEmail = Clients::where('subscriber_id', $subscriberId)
                ->where('email', $invoice->to_email)
                ->first();
            if ($byEmail) {
                return $byEmail;
            }
        }

        if (!empty($invoice->to_name)) {
            return Clients::where('subscriber_id', $subscriberId)
                ->where('name', $invoice->to_name)
                ->first();
        }

        return null;
    }

    private function resolveInvoiceApplication(Internal_Invoices $invoice, ?Clients $client, $subscriberId): ?Applications
    {
        $invoice->loadMissing('items');

        $appRefs = $invoice->items
            ->pluck('application_id')
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values();

        $application = $this->findApplicationByRefs($appRefs, $subscriberId, $client)
            ?: $this->findApplicationByRefs($appRefs, $subscriberId, null);

        if ($application) {
            return $application;
        }

        // Fallback: previous AR payment against this invoice (stores applications.id)
        $paymentAppId = PaymentARs::where('subscriber_id', $subscriberId)
            ->where('type', 'ar')
            ->where('invoice_no', $invoice->invoice_no)
            ->whereNotNull('application_id')
            ->orderBy('created_at')
            ->value('application_id');

        if ($paymentAppId) {
            $application = Applications::where('subscriber_id', $subscriberId)
                ->where(function ($query) use ($paymentAppId) {
                    $query->where('id', $paymentAppId)
                        ->orWhere('application_id', (string) $paymentAppId);
                })
                ->first();
            if ($application) {
                return $application;
            }
        }

        // Fallback: match client applications by service/application name on the invoice
        $details = $invoice->items
            ->pluck('detail')
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->values();

        foreach (preg_split('/\s*,\s*/', (string) ($invoice->detail ?? '')) as $part) {
            $part = trim((string) $part);
            if ($part !== '') {
                $details->push($part);
            }
        }

        $details = $details->unique()->values();
        if ($client && $details->isNotEmpty()) {
            foreach ($details as $detail) {
                $application = Applications::where('subscriber_id', $subscriberId)
                    ->where('client_id', $client->id)
                    ->where(function ($query) use ($detail) {
                        $query->where('application_name', $detail)
                            ->orWhere('application_id', $detail)
                            ->orWhereRaw(
                                "CONCAT(TRIM(application_name), ' (', application_id, ')') = ?",
                                [$detail]
                            )
                            ->orWhere('application_name', 'like', $detail . '%');
                    })
                    ->orderBy('id')
                    ->first();

                if ($application) {
                    return $application;
                }
            }
        }

        // Fallback: client has exactly one known application
        if ($client) {
            $apps = Applications::where('subscriber_id', $subscriberId)
                ->where('client_id', $client->id)
                ->orderBy('id')
                ->limit(2)
                ->get();

            if ($apps->count() === 1) {
                return $apps->first();
            }
        }

        return null;
    }

    private function findApplicationByRefs($appRefs, $subscriberId, ?Clients $client): ?Applications
    {
        if (!$appRefs instanceof \Illuminate\Support\Collection || $appRefs->isEmpty()) {
            return null;
        }

        $codes = $appRefs->all();
        $numericIds = $appRefs
            ->filter(fn ($value) => ctype_digit((string) $value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $query = Applications::where('subscriber_id', $subscriberId)
            ->where(function ($inner) use ($codes, $numericIds) {
                $inner->whereIn('application_id', $codes);
                if (!empty($numericIds)) {
                    $inner->orWhereIn('id', $numericIds);
                }
            });

        if ($client) {
            $query->where('client_id', $client->id);
        }

        return $query->orderBy('id')->first();
    }

    private function calculateOutstandingAmount(PaymentARs $payment, string $type): float
    {
        $query = PaymentARs::where('subscriber_id', $payment->subscriber_id)
            ->where('type', $type)
            ->where('amount', $payment->amount)
            ->where('client_id', $payment->client_id);

        if ($type === 'ar') {
            $query->where('application_id', $payment->application_id)
                ->where('service_description', $payment->service_description);
        } else {
            $query->where('service_provider', $payment->service_provider)
                ->where('service_taken', $payment->service_taken);
        }

        $paidAmount = (float) $query->sum('paid_amount');
        return max(0, ((float) $payment->amount) - $paidAmount);
    }

    public function taxSummaryData(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $user = auth()->user();
            $subscriber = $user->user_type === 'Subscriber'
                ? $user
                : User::find($user->added_by);

            if (!$subscriber) {
                return response()->json([
                    'message' => 'Unable to resolve subscriber account.',
                    'total_collected_tax_formatted' => '0.00',
                    'by_timeline' => [],
                    'by_year' => [],
                ], 403);
            }

            $summary = $this->taxSummaryService->summary($subscriber);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'total_collected_tax' => $summary['total_collected_tax'],
                'total_collected_tax_formatted' => $summary['total_collected_tax_formatted'],
                'by_timeline' => $summary['by_timeline'],
                'by_year' => $summary['by_year'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load tax summary.',
                'total_collected_tax_formatted' => '0.00',
                'by_timeline' => [],
                'by_year' => [],
            ], 500);
        }
    }

}
