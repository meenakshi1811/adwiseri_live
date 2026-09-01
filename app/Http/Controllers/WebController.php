<?php

namespace App\Http\Controllers;
// date_default_timezone_set("Asia/Kolkata");
use DB;
use Auth;
use Hash;
use App;
use Mail;
use Log;
use Session;
use Cookie;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use DateTime;
use DateTimeZone;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Mail\EmailVerification;
use App\Mail\SupportMail;
use App\Mail\WelcomeMail;
use App\Mail\SubscriptionMail;
use App\Mail\PlanSubscriptionMail;
use App\Mail\AppointmentSchedulerMail;
use App\Mail\AppointmentResponseMail;
use App\Mail\ClientCareLetterMail;
use App\Support\BrandedMail;
use App\Support\PhoneNumber;
use App\Services\SubscriptionTermPricing;
use App\Services\TableFilterCountService;

use App\Models\User;
use App\Models\Clients;
use App\Models\Client_Docs;
use App\Models\Currency;
use App\Models\Countries;
use App\Models\Services;
use App\Models\States;
use App\Models\Subscriber_Categories;
use App\Models\Subscriber_Sub_Categories;
use App\Models\Contactus;
use App\Models\Features;
use App\Models\Membership;
use App\Models\About_Advisori;
use App\Models\Invoices;
use App\Models\Internal_Invoices;
use App\Models\Job_roles;
use App\Models\Activities;
use App\Models\Client_jobs;
use App\Models\Messages;
use App\Models\Referrals;
use App\Models\Applications;
use App\Models\Tickets;
use App\Models\MyTimezones;
use App\Models\Faq;
use App\Models\Invoice_settings;
use App\Services\InvoiceAuditService;
use App\Services\InvoiceItemService;
use App\Services\InvoiceMailService;
use App\Services\InvoiceSnapshotService;
use App\Services\OfferBenefitService;
use App\Models\Used_referrals;
use App\Models\AffiliateCommissionEarnt;
use App\Models\Application_assignments;
use App\Models\ApplicationStatusTrack;
use App\Models\Internal_communications;
use App\Models\Client_discussions;
use App\Models\EmailSubscriptions;
use App\Models\DemoRequests;
use App\Models\UserRoles;
use App\Models\Affiliates;
use App\Models\Feedbacks;
use App\Models\PaymentARs;
use App\Models\Offers;
use App\Models\LandingPromoItem;
use App\Models\LandingPromoSetting;
use App\Models\HomepageSectionSetting;
use App\Models\Appointment;

use App\Exports\UsersExport;
use App\Exports\ClientsExport;
use Maatwebsite\Excel\Facades\Excel;
use DataTables;
/*Newly added models on 2026-03-06 by Meenakshi Nanta*/
use App\Models\VisaEnquiry;
use App\Models\EnquiryResidencyHistory;
use App\Models\EnquiryTravelHistory;
use App\Models\EnquiryRefusalHistory;
use App\Models\EnquiryWorkExperience;
use App\Models\EnquiryChild;
use App\Models\EnquiryFundingSource;
use App\Models\Dependants;
use App\Models\ReportSetting;
use App\Models\PaymentReminderSetting;
use App\Models\ApplicationReminder;
use App\Models\UserSession;
use App\Services\EmailTemplateService;
use App\Services\EmailBroadcastService;
use App\Services\CountryCategorySettingsService;
use App\Services\DashboardPreferenceService;
use App\Services\EnquiryFormSettingsService;
use App\Services\AppointmentService;
use App\Services\ApplicationVisibilityService;
use App\Services\MySettingsPageService;
use App\Services\LeadEnquiryService;
use App\Services\UserAccessRightsService;
use App\Services\NotificationService;
class WebController extends Controller
{
    private const APPLICATION_STATUS_FLOW = ['Client Registered', 'Client Counselled', 'Preparation', 'Appointment Booked', 'Applied', 'Decision', 'Appeal Lodged', 'Appeal Decision', 'AR / JR Lodged', 'AR / JR Decision', 'Withdrawn', 'Cancelled'];

    private const APPLICATION_END_DATE_REQUIRED_STATUSES = ['Decision', 'Appeal Decision', 'AR / JR Decision', 'Withdrawn', 'Cancelled'];

    private function normalizeDateValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = is_string($value) ? trim($value) : $value;

        if ($value === '') {
            return null;
        }

        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, (string) $value);
                if ($date && $date->format($format) === (string) $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        if (is_string($value) && preg_match('/^\d{1,2}$/', $value)) {
            $month = (int) $value;
            if ($month >= 1 && $month <= 12) {
                return Carbon::create(Carbon::now()->year, $month, 1)->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeDateTimeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = is_string($value) ? trim($value) : $value;
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', (string) $value);
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeDateArray($dates): array
    {
        if (!is_array($dates)) {
            return [];
        }

        return array_map(function ($date) {
            return $this->normalizeDateValue($date);
        }, $dates);
    }

    private function normalizeCountryPreferences($countryPreferences): array
    {
        if (!is_array($countryPreferences)) {
            return [null, null, null];
        }

        $normalizedPreferences = collect($countryPreferences)
            ->map(fn ($country) => trim((string) $country))
            ->filter()
            ->unique()
            ->values()
            ->take(3)
            ->all();

        return [
            $normalizedPreferences[0] ?? null,
            $normalizedPreferences[1] ?? null,
            $normalizedPreferences[2] ?? null,
        ];
    }

    private function getSubscriberCountryOptions(int $subscriberId, array $selectedCountries = [])
    {
        return app(CountryCategorySettingsService::class)
            ->getSubscriberCountryOptions($subscriberId, $selectedCountries);
    }

    private function generateInternalInvoiceId(): string
    {
        $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $id = "";
        for ($i = 0; $i < 8; $i++) {
            $id .= $ch[rand(0, strlen($ch) - 1)];
        }

        if (Internal_Invoices::where('invoice_no', '=', $id)->exists()) {
            return $this->generateInternalInvoiceId();
        }

        return $id;
    }

    private function generateInternalInvoiceToken(): string
    {
        $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $token = "";
        for ($i = 0; $i < 20; $i++) {
            $token .= $ch[rand(0, strlen($ch) - 1)];
        }

        if (Internal_Invoices::where('token', '=', $token)->exists()) {
            return $this->generateInternalInvoiceToken();
        }

        return $token;
    }

    private function writeExportCsv($filePath, $rows)
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            return;
        }

        if (count($rows) > 0) {
            $headers = array_keys((array) $rows[0]);
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_values((array) $row));
            }
        }

        fclose($handle);
    }

    private function safeArchiveName($name)
    {
        return trim(preg_replace('/[^A-Za-z0-9\-_. ]/', '', (string) $name)) ?: 'Unknown';
    }

    private function createAdminApInvoiceAndPayment(User $subscriber, User $company, float $amount, string $paymentMode, string $detail = 'Subscription Fees'): Internal_Invoices
    {
        $amount = round(max(0, $amount), 2);

        $internalInvoice = new Internal_Invoices();
        $internalInvoice->invoice_no = $this->generateInternalInvoiceId();
        $internalInvoice->subscriber_id = $subscriber->id;
        $internalInvoice->name = $company->organization;
        $internalInvoice->email = $company->email;
        $internalInvoice->phone = $company->phone;
        $internalInvoice->country = $company->country;
        $internalInvoice->state = $company->state;
        $internalInvoice->city = $company->city;
        $internalInvoice->pincode = $company->pincode;
        $internalInvoice->address = $company->address_line;
        $internalInvoice->logo = $company->organization_logo;
        $internalInvoice->vendor_id = Internal_Invoices::ADWISERI_VENDOR_ID;
        $internalInvoice->to_name = Internal_Invoices::ADWISERI_VENDOR_NAME;
        $internalInvoice->to_email = $subscriber->email;
        $internalInvoice->to_phone = $subscriber->phone;
        $internalInvoice->to_country = $subscriber->country;
        $internalInvoice->to_state = $subscriber->state;
        $internalInvoice->to_city = $subscriber->city;
        $internalInvoice->to_pincode = $subscriber->pincode;
        $internalInvoice->to_address = $subscriber->address_line;
        $internalInvoice->detail = $detail;
        $internalInvoice->amount = $amount;
        $internalInvoice->discount = 0;
        $internalInvoice->tax = 0;
        $internalInvoice->total = $amount;
        $internalInvoice->status = 'Paid';
        $internalInvoice->type = 'ap';
        $internalInvoice->due_date = date('Y-m-d');
        $internalInvoice->token = $this->generateInternalInvoiceToken();
        $actingUser = Auth::user() ?: $subscriber;
        app(InvoiceAuditService::class)->markCreated($internalInvoice, $actingUser);
        $internalInvoice->save();

        PaymentARs::create([
            'subscriber_id' => $subscriber->id,
            'invoice_no' => $internalInvoice->invoice_no,
            'service_provider' => 'adwiseri.com',
            'service_taken' => $detail,
            'amount' => $amount,
            'paid_amount' => $amount,
            'payment_mode' => 'Online',
            'payment_date' => now(),
            'type' => 'ap',
        ]);

        return $internalInvoice;
    }

    private function buildInvoicePdfData(Internal_Invoices $internalInvoice, User $subscriber, User $company): object
    {
        return \App\Services\InternalInvoicePdfDataFactory::make($internalInvoice, $subscriber, $company);
    }

    private function sendPlanUpdateMail(
        User $subscriber,
        ?Membership $plan,
        Internal_Invoices $internalInvoice,
        User $company,
        ?string $previousPlanName = null,
        ?int $durationYears = null,
        ?string $purchaseCategory = null
    ): bool {
        if (!\App\Support\EmailAddress::isValidRecipient($subscriber->email)) {
            \Illuminate\Support\Facades\Log::warning('Plan update email skipped: invalid recipient', [
                'email' => $subscriber->email,
                'user_id' => $subscriber->id,
            ]);

            return false;
        }

        $paidAmount = $internalInvoice->total ?? $internalInvoice->amount ?? null;

        try {
            Mail::to($subscriber->email)->send(new PlanSubscriptionMail(
                $subscriber->name,
                $plan->plan_name ?? $subscriber->membership,
                $plan->validity ?? 'N/A',
                'Your Subscription Plan Has Been Updated',
                $this->buildInvoicePdfData($internalInvoice, $subscriber, $company),
                $paidAmount,
                $previousPlanName,
                $durationYears,
                $purchaseCategory
            ));

            return !Mail::failures();
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('Plan update email failed', [
                'email' => $subscriber->email,
                'user_id' => $subscriber->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function recordWalletSubscriptionDebit(
        User $user,
        Membership $plan,
        float $debitAmount,
        float $previousBalance,
        ?string $previousPlanName = null
    ): void {
        $description = app(\App\Services\WalletLedgerService::class)
            ->subscriptionDebitDescription($previousPlanName, $plan);

        app(\App\Services\WalletLedgerService::class)->recordSubscriptionDebit(
            $user,
            $debitAmount,
            $previousBalance,
            (float) $user->wallet,
            $description
        );
    }

    private function processRenewalCommissionIfEligible(
        User $user,
        float $paymentAmount,
        ?\Carbon\Carbon $previousExpiry,
        ?string $previousPlanName,
        string $newPlanName
    ): void {
        if ($paymentAmount <= 0) {
            return;
        }

        $journeyLog = app(\App\Services\UserJourneyLogService::class);
        $purchaseCategory = $journeyLog->classifySubscriptionPurchase(
            false,
            $previousExpiry,
            $previousPlanName,
            $newPlanName
        );

        app(\App\Services\RenewalCommissionService::class)->processRenewalCommission(
            $user,
            $paymentAmount,
            $purchaseCategory,
            $previousExpiry
        );
    }

    public function add_subscriber_roles()
    {
        $subscribers = User::where('user_type', '=', "Subscriber")->get();
        foreach ($subscribers as $staff) {
            $role = UserRoles::where('user_id', '=', $staff->id)->get();
            if ($role) {
                foreach ($role as $r) {
                    $r->delete();
                }
            }

            $clients = new UserRoles();
            $clients->user_id = $staff->id;
            // $clients->subscriber_id = $staff->added_by;
            $clients->name = $staff->name;
            $clients->email = $staff->email;
            $clients->module = "Clients";
            $clients->read_only = 1;
            $clients->write_only = 1;
            $clients->update_only = 1;
            $clients->delete_only = 1;
            $clients->read_write_only = 1;
            $clients->save();

            $applications = new UserRoles();
            $applications->user_id = $staff->id;
            // $applications->subscriber_id = $staff->added_by;
            $applications->name = $staff->name;
            $applications->email = $staff->email;
            $applications->module = "Applications";
            $applications->read_only = 1;
            $applications->write_only = 1;
            $applications->update_only = 1;
            $applications->delete_only = 1;
            $applications->read_write_only = 1;
            $applications->save();

            $communication = new UserRoles();
            $communication->user_id = $staff->id;
            // $communication->subscriber_id = $staff->added_by;
            $communication->name = $staff->name;
            $communication->email = $staff->email;
            $communication->module = "Communication";
            $communication->read_only = 1;
            $communication->write_only = 1;
            $communication->update_only = 1;
            $communication->delete_only = 1;
            $communication->read_write_only = 1;
            $communication->save();

            $invoices = new UserRoles();
            $invoices->user_id = $staff->id;
            // $invoices->subscriber_id = $staff->added_by;
            $invoices->name = $staff->name;
            $invoices->email = $staff->email;
            $invoices->module = "Invoices";
            $invoices->read_only = 1;
            $invoices->write_only = 1;
            $invoices->update_only = 1;
            $invoices->delete_only = 1;
            $invoices->read_write_only = 1;
            $invoices->save();

            $payments = new UserRoles();
            $payments->user_id = $staff->id;
            // $payments->subscriber_id = $staff->added_by;
            $payments->name = $staff->name;
            $payments->email = $staff->email;
            $payments->module = "Payments";
            $payments->read_only = 1;
            $payments->write_only = 1;
            $payments->update_only = 1;
            $payments->delete_only = 1;
            $payments->read_write_only = 1;
            $payments->save();

            $reports = new UserRoles();
            $reports->user_id = $staff->id;
            // $reports->subscriber_id = $staff->added_by;
            $reports->name = $staff->name;
            $reports->email = $staff->email;
            $reports->module = "Reports";
            $reports->read_only = 1;
            $reports->write_only = 1;
            $reports->update_only = 1;
            $reports->delete_only = 1;
            $reports->read_write_only = 1;
            $reports->save();

            $subscription = new UserRoles();
            $subscription->user_id = $staff->id;
            // $subscription->subscriber_id = $staff->added_by;
            $subscription->name = $staff->name;
            $subscription->email = $staff->email;
            $subscription->module = "Subscription";
            $subscription->read_only = 1;
            $subscription->write_only = 1;
            $subscription->update_only = 1;
            $subscription->delete_only = 1;
            $subscription->read_write_only = 1;
            $subscription->save();

            $settings = new UserRoles();
            $settings->user_id = $staff->id;
            // $settings->subscriber_id = $staff->added_by;
            $settings->name = $staff->name;
            $settings->email = $staff->email;
            $settings->module = "Settings";
            $settings->read_only = 1;
            $settings->write_only = 1;
            $settings->update_only = 1;
            $settings->delete_only = 1;
            $settings->read_write_only = 1;
            $settings->save();

            $support = new UserRoles();
            $support->user_id = $staff->id;
            // $support->subscriber_id = $staff->added_by;
            $support->name = $staff->name;
            $support->email = $staff->email;
            $support->module = "Support";
            $support->read_only = 1;
            $support->write_only = 1;
            $support->update_only = 1;
            $support->delete_only = 1;
            $support->read_write_only = 1;
            $support->save();
        }
    }
    public function export_users()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function export_clients()
    {
        return Excel::download(new ClientsExport, 'clients.xlsx');
    }

    public function set_timezone()
    {
        $user = Auth::user();
        if ($user) {
            // date_default_timezone_set($user->timezone);
        }
    }

    public function check_login()
    {
        $user = Auth::user();
        if( $user->status == 'true'){
            if ($user) {
                return $user;
            } else {
                $user = auth()->guard('affiliates')->user();
                if ($user) {
                    $user = User::where('status', 'true')->where('email', $user->email)->first();
                    $user['type_user'] = 'affiliate';
                    return $user;
                }
                Auth::logout();
                Session::flush();
                return redirect()->route('login');
            }
        }
        Auth::logout();
        Session::flush();
        return redirect()->route('login');
    }

    public function index()
    {
        $user = Auth::user();
        if ($user) {
            if ($user->user_type == 'admin') {
                return redirect()->route('admin_profile');
            }
            if (membership_access_blocked($user)) {
                return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
            }
            $this->set_timezone();
            $page = "index";
            $price_plans = Membership::orderBy('created_at', 'asc')->get();
            $features = Features::get();
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $myplan = Membership::where('plan_name', '=', $user->membership)->first();
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
                $myplan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            }
            $total_users = User::where('added_by', '=', $subscriber->id)->get();
            $total_clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            $discounts = Offers::get();
            [$landingPromoSettings, $landingDiscountItems, $landingOfferItems] = $this->landingPromoPayload();
            $homepageSectionVisibility = HomepageSectionSetting::current()->visibilityMap();
            return view('web.index', compact(
                'user',
                'page',
                'features',
                'price_plans',
                'myplan',
                'subscriber',
                'total_users',
                'total_clients',
                'discounts',
                'landingPromoSettings',
                'landingDiscountItems',
                'landingOfferItems',
                'homepageSectionVisibility'
            ));
        } else {
            $page = "index";
            $price_plans = Membership::orderBy('created_at', 'asc')->get();
            $features = Features::get();
            $myplan = null;
            $subscriber = null;
            $total_users = 0;
            $total_clients = 0;
            $discounts = Offers::get();
            [$landingPromoSettings, $landingDiscountItems, $landingOfferItems] = $this->landingPromoPayload();
            $homepageSectionVisibility = HomepageSectionSetting::current()->visibilityMap();
            return view('web.index', compact(
                'page',
                'features',
                'price_plans',
                'myplan',
                'subscriber',
                'total_users',
                'total_clients',
                'discounts',
                'landingPromoSettings',
                'landingDiscountItems',
                'landingOfferItems',
                'homepageSectionVisibility'
            ));
            // return redirect()->route('login');
        }
    }

    /**
     * Landing-page Discounts & Offers section data (admin-managed).
     */
    private function landingPromoPayload(): array
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('landing_promo_settings')) {
                return [null, collect(), collect()];
            }

            $settings = LandingPromoSetting::current();
            $discountItems = LandingPromoItem::ofCategory('discount')->active()->orderBy('sort_order')->orderBy('id')->get();
            $offerItems = LandingPromoItem::ofCategory('offer')->active()->orderBy('sort_order')->orderBy('id')->get();

            return [$settings, $discountItems, $offerItems];
        } catch (\Throwable $e) {
            return [null, collect(), collect()];
        }
    }

    public function send_email()
    {
        $data["email"] = "sandeepkumarsangwal21@gmail.com";
        $data["title"] = "From Sandeep";
        $data["body"] = "This is Demo for file";

        $files = [
            asset('web_assets/images/50count.png'),
            asset('web_assets/images/100client.png'),
        ];
        Mail::send('web.subscriptiontemplate', $data, function ($message) use ($data, $files) {
            $message->to($data["email"], $data["email"])
                ->subject("adwiseri Subscription Added");

            foreach ($files as $file) {
                $message->attach($file);
            }
        });
        // Mail::to("sandeepkumarsangwal21@gmail.com")->send(new SubscriptionMail());
        if (Mail::failures()) {
            echo "Mail not Sent";
        } else {
            echo "Success";
        }
    }

    public function get_demo()
    {
        $countries = Countries::get();
        return view('web.book_demo', compact('countries'));
    }

    public function demo_post(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|string|max:255',
                'phone' => 'required|phone_intl|unique:demo_requests',
                'email' => 'required|string|email|max:255|unique:demo_requests',
                'country' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'how_did_hear' => 'required|in:LinkedIn,Twitter,YouTube,Industry friend,Google',
                'terms' => 'accepted',
                'g-recaptcha-response' => 'required|captcha'
            ]
        );
        $demo = new DemoRequests();
        $demo->name = $request['name'];
        $demo->phone = $request['phone'];
        $demo->email = $request['email'];
        $demo->country = $request['country'];
        $demo->city = $request['city'];
        $demo->terms_accepted_at = now();
        $demo->status = "Pending";
        $demo->company_name = $request['company_name'];
        $demo->job_title = $request['job_title'];
        $demo->how_did_hear = $request['how_did_hear'];
        $demo->save();
        foreach (BrandedMail::adminNotificationRecipients() as $recipient) {
            Mail::to($recipient)->send(new EmailVerification($demo));
        }
        if (Mail::failures()) {
            echo 'Sorry! Please try again latter';
        } else {
            echo 'Success';
            return back()->with('submitted', 'Demo request submitted successfully.');
        }
    }

    public function validatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:25',
        ]);

        $normalized = PhoneNumber::normalize($request->phone);
        $valid = PhoneNumber::isValid($normalized);

        return response()->json([
            'valid' => $valid,
            'phone' => $valid ? $normalized : null,
            'message' => $valid ? null : 'Please enter a valid contact number (digits only, up to 10 digits after country code).',
        ]);
    }

    public function email_subscription(Request $request)
    {
        $validated = $this->validate($request, [
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $existing = EmailSubscriptions::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if ($existing && $existing->isSubscribed()) {
            return redirect()->route('/')->with('subscribed', 'This email is already subscribed.');
        }

        try {
            app(\App\Services\EmailSubscriptionService::class)->subscribe($email);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('/')->with('subscription_error', 'Could not complete subscription. Please try again later.');
        }

        try {
            Mail::to($email)->send(new SubscriptionMail($email));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('/')->with('subscribed', 'Email subscription submitted successfully.');
    }

    public function emailtemplate()
    {
        $user = Auth::user();
        $this->set_timezone();
        return view('web.emailtemplate');
    }

    public function login()
    {

        if (Auth::user()) {
            $user = Auth::user();
            if ($user->status != "true") {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')->with('deactivated', "Your account has been deactivated.");
            }
            $this->set_timezone();
            if ($user->organization != "") {
                if (membership_access_blocked($user)) {
                    return redirect()->route('userprofile')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
                } else {
                    return redirect()->route('userprofile');
                }
            } else {
                $countries = Countries::all();
                $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                $states = States::all();
                $page = "index";
                return view('web.moredetails', compact('user', 'countries', 'states', 'page', 'tzlist'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function user_register($ref = null)
    {
        // $this->set_timezone();
        if ($ref != null) {
            $referral = $ref;
        } else {
            $referral = null;
        }
        $splan = null;
        $countries = Countries::get();
        $subscriber_categories = Subscriber_Categories::get();
        $membership = Membership::orderBy('price_per_year')->get();
        return view('web.register', compact('subscriber_categories', 'countries', 'membership', 'referral', 'splan'));
    }

    public function user_register_plan($plan = null)
    {
        // $this->set_timezone();
        if ($plan != null) {
            $splan = $plan;
        } else {
            $splan = null;
        }
        $referral = null;
        $countries = Countries::get();
        $subscriber_categories = Subscriber_Categories::get();
        $membership = Membership::orderBy('price_per_year')->get();
        return view('web.register', compact('subscriber_categories', 'countries', 'membership', 'referral', 'splan'));
    }

    public function check_registration(Request $request)
    {

        $this->validate(
            $request,
            [
                'name' => 'required|string|max:255',
                'phone' => 'required|phone_intl|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'category' => 'required|string|max:255',
                'subcategory' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]
        );
        if ($request->referral != null) {
            $find_referral = User::where('referral', '=', $request->referral)->first();
            if (!$find_referral) {
                return redirect()->back()->withInput()->withError('Invalid Referral Code');
            }
        }
        if ($request->membership != "Free") {
            $request->validate([
                'duration' => 'nullable|integer|in:' . implode(',', SubscriptionTermPricing::allowedDurations()),
            ]);

            $data = $request->all();
            $data['duration'] = SubscriptionTermPricing::normalizeDuration((int) $request->input('duration', 1));
            Session::put('reg_data', $data);
            return redirect()->route('reg_pay');
        } else {
            $data = $request->all();
            Session::put('reg_data', $data);
            return redirect()->route('user_registration');
        }
    }

    public function reg_pay(Request $request)
    {
        $data = session('reg_data');
        if (!$data || empty($data['membership']) || ($data['membership'] ?? '') === 'Free') {
            return redirect()->route('user_register');
        }

        if ($request->filled('duration')) {
            $data['duration'] = SubscriptionTermPricing::normalizeDuration((int) $request->input('duration'));
            Session::put('reg_data', $data);
        }

        $plan = $data['membership'];
        $duration = SubscriptionTermPricing::normalizeDuration((int) ($data['duration'] ?? 1));

        $membership = Membership::where('plan_name', '=', $plan)->first();
        if (!$membership) {
            return redirect()->route('user_register')->withInput()->withError('Selected plan is no longer available.');
        }

        $amount = SubscriptionTermPricing::calculate((float) $membership->price_per_year, $duration);
        $data['duration'] = $duration;
        $data['amount'] = $amount;
        Session::put('reg_data', $data);

        return view('web.reg_pay', compact('amount', 'membership', 'duration'));
    }


    public function user_registration()
    {

        $request = session('reg_data');
        $data = new User();
        // $this->validate($request,
        // [
        //     'name' => 'required|string|max:255',
        //     'phone' => 'required|string|min:9|max:12|unique:users',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'category' => 'required|string|max:255',
        //     'subcategory' => 'required|string|max:255',
        //     'password' => 'required|string|min:8|confirmed',
        // ]);
        // if($request->referral != null){
        //     $find_referral = User::where('referral','=',$request->referral)->first();
        //     if(!$find_referral){
        //         return redirect()->back()->withInput()->withError('Invalid Referral Code');
        //     }
        // }

        if ($request['referral'] != null) {
            $find_referral = User::where('referral', '=', $request['referral'])->first();
        }
        $eotp = rand(10000, 99999);
        $plan = Membership::where('plan_name', '=', $request['membership'])->first();
        $data->user_type = "Subscriber";
        $data->name = $request['name'];
        $data->phone = $request['phone'];
        $data->email = $request['email'];
        $data->category = $request['category'];
        $data->sub_category = $request['subcategory'];
        $data->other_subcategory = $request['other'];
        // $data->membership = $request['membership'];
        $data->membership = $plan->plan_name;
        $data->membership_type = "Free";
        // if($request->membership == "Free"){
        // }
        // else{
        //     $data->membership_type = "Trial";
        //     $data->membership_expiry_date = (new DateTime("now"))->modify("+30 days");
        // }
        $enddate = (new DateTime("now"))->modify("+" . $plan->validity . " Days");
        $data->membership_start_date = new DateTime("now");
        $data->membership_expiry_date = $enddate;
        $data->wallet = 0;
        $data->referral = $this->get_referral();
        $data->referral_code = $request['referral'];
        $data->email_otp = $eotp;
        $data->timezone = "UTC";
        $data->status = "true";
        $data->password = Hash::make($request['password']);
        $data->save();

        app(OfferBenefitService::class)->applyEligibleNewSubscriberOffers($data);

        $company = User::where('user_type', '=', 'admin')->first();
        $signupInvoiceAmount = strtolower((string) $plan->plan_name) === 'free'
            ? 0.0
            : (float) ($request['amount'] ?? $plan->price_per_year ?? 0);
        $signupDuration = SubscriptionTermPricing::normalizeDuration((int) ($request['duration'] ?? 1));
        $internalInvoice = null;
        if ($company) {
            $internalInvoice = $this->createAdminApInvoiceAndPayment(
                $data,
                $company,
                $signupInvoiceAmount,
                "Manual",
                SubscriptionTermPricing::subscriptionFeeDetail($plan->plan_name, $signupDuration)
            );
        }

        $role = UserRoles::where('user_id', '=', $data->id)->get();
        if ($role) {
            foreach ($role as $r) {
                $r->delete();
            }
        }
        $clients = new UserRoles();
        $clients->user_id = $data->id;
        // $clients->subscriber_id = '';
        $clients->name = $data->name;
        $clients->email = $data->email;
        $clients->module = "Clients";
        $clients->read_only = 1;
        $clients->write_only = 1;
        $clients->update_only = 1;
        $clients->delete_only = 1;
        $clients->read_write_only = 1;
        $clients->save();

        $applications = new UserRoles();
        $applications->user_id = $data->id;
        // $applications->subscriber_id = '';
        $applications->name = $data->name;
        $applications->email = $data->email;
        $applications->module = "Applications";
        $applications->read_only = 1;
        $applications->write_only = 1;
        $applications->update_only = 1;
        $applications->delete_only = 1;
        $applications->read_write_only = 1;
        $applications->save();

        $communication = new UserRoles();
        $communication->user_id = $data->id;
        // $communication->subscriber_id = '';
        $communication->name = $data->name;
        $communication->email = $data->email;
        $communication->module = "Communication";
        $communication->read_only = 1;
        $communication->write_only = 1;
        $communication->update_only = 1;
        $communication->delete_only = 1;
        $communication->read_write_only = 1;
        $communication->save();

        $invoices = new UserRoles();
        $invoices->user_id = $data->id;
        // $invoices->subscriber_id = '';
        $invoices->name = $data->name;
        $invoices->email = $data->email;
        $invoices->module = "Invoices";
        $invoices->read_only = 1;
        $invoices->write_only = 1;
        $invoices->update_only = 1;
        $invoices->delete_only = 1;
        $invoices->read_write_only = 1;
        $invoices->save();

        $payments = new UserRoles();
        $payments->user_id = $data->id;
        // $payments->subscriber_id = '';
        $payments->name = $data->name;
        $payments->email = $data->email;
        $payments->module = "Payments";
        $payments->read_only = 1;
        $payments->write_only = 1;
        $payments->update_only = 1;
        $payments->delete_only = 1;
        $payments->read_write_only = 1;
        $payments->save();

        $reports = new UserRoles();
        $reports->user_id = $data->id;
        // $reports->subscriber_id = '';
        $reports->name = $data->name;
        $reports->email = $data->email;
        $reports->module = "Reports";
        $reports->read_only = 1;
        $reports->write_only = 1;
        $reports->update_only = 1;
        $reports->delete_only = 1;
        $reports->read_write_only = 1;
        $reports->save();

        $subscription = new UserRoles();
        $subscription->user_id = $data->id;
        // $subscription->subscriber_id = '';
        $subscription->name = $data->name;
        $subscription->email = $data->email;
        $subscription->module = "Subscription";
        $subscription->read_only = 1;
        $subscription->write_only = 1;
        $subscription->update_only = 1;
        $subscription->delete_only = 1;
        $subscription->read_write_only = 1;
        $subscription->save();

        $settings = new UserRoles();
        $settings->user_id = $data->id;
        // $settings->subscriber_id = '';
        $settings->name = $data->name;
        $settings->email = $data->email;
        $settings->module = "Settings";
        $settings->read_only = 1;
        $settings->write_only = 1;
        $settings->update_only = 1;
        $settings->delete_only = 1;
        $settings->read_write_only = 1;
        $settings->save();

        $support = new UserRoles();
        $support->user_id = $data->id;
        // $support->subscriber_id = '';
        $support->name = $data->name;
        $support->email = $data->email;
        $support->module = "Support";
        $support->read_only = 1;
        $support->write_only = 1;
        $support->update_only = 1;
        $support->delete_only = 1;
        $support->read_write_only = 1;
        $support->save();


        $activity = new Activities();
        $activity->user_id = $data->id;
        $activity->user_name = $data->name;
        $activity->activity_name = "New Subscriber Added";
        $activity->activity_detail = "New Subscriber " . $data->name . " registered at " . $request['local_time'];
        $activity->activity_icon = "user.png";
        $activity->local_time = $request['local_time'];
        $activity->save();
        if (isset($find_referral) &&  $plan->plan_name != 'Free') {
            $wallet = $find_referral->wallet;
            $find_referral->wallet = $wallet + 10;
            $find_referral->save();

            $save_referral = new Referrals();
            $save_referral->referral_code = $request['referral'];
            $save_referral->userid = $data->id;
            $save_referral->user_name = $data->name;
            $save_referral->total_amount = 10;
            $save_referral->amount_added = 10;
            $save_referral->previous_balance = $wallet;
            $save_referral->wallet_balance = $wallet + 10;
            $save_referral->save();

            $use_referral = new Used_referrals();
            $use_referral->referral_code = $request['referral'];
            $use_referral->subscriber_id = $data->id;
            $use_referral->commission_earnt = 10;
            $use_referral->save();

            $affiliate_commission = AffiliateCommissionEarnt::where('referral_code', $request['referral'])->first();
            if ($affiliate_commission) {

                $affiliate_commission->total_earned += 10;
                $affiliate_commission->save();
            } else {

                $use_referral = new AffiliateCommissionEarnt();
                $use_referral->referral_code = $request['referral'];
                $use_referral->total_earned = 10;
                $use_referral->save();
            }
        }
        $email = $data->email;
        $welcomedata = new \stdClass();
        $welcomedata->id = $data->id;
        $welcomedata->organization_logo = $data->organization_logo;
        $welcomedata->name = $data['name'];
        $welcomedata->email = $email;
        $welcomedata->plan_name = $plan->plan_name;
        $welcomedata->duration = $plan->validity . " Days";
        $welcomedata->amount = number_format((float) $signupInvoiceAmount, 2);
        $welcomedata->subscription_type = $plan->plan_name;
        $welcomedata->start_date = !empty($data->membership_start_date)
            ? (($data->membership_start_date instanceof \DateTimeInterface)
                ? $data->membership_start_date->format('d-m-Y')
                : date("d-m-Y", strtotime((string) $data->membership_start_date)))
            : '-';
        $welcomedata->end_date = !empty($data->membership_expiry_date)
            ? (($data->membership_expiry_date instanceof \DateTimeInterface)
                ? $data->membership_expiry_date->format('d-m-Y')
                : date("d-m-Y", strtotime((string) $data->membership_expiry_date)))
            : '-';
        $welcomedata->paid_amount = number_format((float) $signupInvoiceAmount, 2);

        if ($company) {
            $welcomedata->from_email = $company->email;
            $welcomedata->from_name = $company->organization ?: 'adwiseri';
        }
        if ($internalInvoice && $company) {
            $welcomedata->invoice_id = $internalInvoice->id;
            $welcomedata->token = $internalInvoice->token;
            $welcomedata->invoice_pdf_data = $this->buildInvoicePdfData($internalInvoice, $data, $company);
        }
        try {
            Mail::to($email)->send(new WelcomeMail($welcomedata));
        } catch (\Exception $e) {
            \Log::error('Welcome mail failed: ' . $e->getMessage());
        }
        if (Mail::failures()) {
            echo 'Sorry! Please try again latter';
        } else {
            echo 'Your email was sent successfully.';
        }

        $email = $request['email'];
        $maildata = new \stdClass();
        $maildata->name = $request['name'];
        $maildata->email = $email;
        $maildata->otp = $eotp;
        // return view('web.emailtemplate',compact('maildata'));
        try {
            Mail::to($email)->send(new EmailVerification($maildata));
        } catch (\Exception $e) {
            \Log::error('Verification mail failed: ' . $e->getMessage());
        }
        if (Mail::failures()) {
            echo 'Sorry! Please try again latter';
        } else {
            echo 'Your email was sent successfully.';
        }
        // $phone_otp = $request->phone_otp;
        // $email_otp = $request->email_otp;
        // return view('web.otp',compact('phone_otp','email_otp'));
        return redirect()->route('otp', $email);
    }

    public function send_otp(Request $request)
    {
        $eotp = rand(10000, 99999);
        $email = $request['email'];
        $usr = User::where('email', '=', $email)->first();
        if ($usr) {
            $usr->email_otp = $eotp;
            $usr->save();
            $maildata = new \stdClass();
            $maildata->name = $usr->name;
            $maildata->email = $email;
            $maildata->otp = $eotp;
            // return view('web.emailtemplate',compact('maildata'));
            Mail::to($email)->send(new EmailVerification($maildata));
            if (Mail::failures()) {
                echo 'Error';
            } else {
                echo 'Success';
            }
        }
    }

    public function get_states(Request $request)
    {
        // print_r($request->all());
        $country = $request['country'];
        // echo $country;
        // echo gettype($request['country']);
        $states = States::where('country_id', '=', $country)->orderBy('name')->get();
?>
        <option value="">Select State</option>
        <?php
        foreach ($states as $state) {
        ?>
            <option value="<?php echo $state->name; ?>"><?php echo $state->name; ?></option>
        <?php
        }
    }

    public function get_timezone(Request $request)
    {
        $country = Countries::find($request['country']);
        if (!$country) {
            return;
        }

        $code = $country->country_code;
        $zones = MyTimezones::where('CountryCode', '=', $code)->get();
        ?>
        <?php
        foreach ($zones as $zone) {
        ?>
            <option value="<?php echo $zone->TimeZone; ?>"><?php echo $zone->TimeZone; ?></option>
        <?php
        }
    }

    public function get_application(Request $request)
    {
        // print_r($request->all());
       
        $id = $request['id'];
        $client = Clients::find($id);

        if (!$client) {
            return '<option value="">Select Application/Service Type</option>';
        }

        $subscriber = User::find($client->subscriber_id);
        $ccService = app(CountryCategorySettingsService::class);
        $visibility = app(ApplicationVisibilityService::class);
        $user = Auth::user();
        $comm = strtolower(trim((string) ($request->comm ?? '')));

        // Meeting notes / communications — simple application list
        if ($comm === 'communication') {
            $applications = $visibility->queryForUser($user, $subscriber)
                ->where('client_id', $id)
                ->orderBy('application_name')
                ->get();

            $html = '<option value="">Select Application</option>';
            foreach ($applications as $app) {
                $typeName = trim((string) ($app->application_name ?? ''));
                $country = trim((string) ($app->visa_country ?? ''));
                $name = $ccService->formatApplicationServiceName($country, $typeName);
                $appId = (string) ($app->application_id ?? '');
                $value = $appId !== '' ? $appId : $name;
                if ($value === '') {
                    continue;
                }

                $label = $name !== '' ? $name . ' (' . $appId . ')' : $appId;
                $html .= '<option value="' . e($value) . '">' . e($label) . '</option>';
            }

            return $html;
        }

        // Invoice form — applications as "Country - Type" + standalone services, with fees
        if ($comm === 'invoice' && $subscriber) {
            $applications = $visibility->queryForUser($user, $subscriber)
                ->where('client_id', $id)
                ->orderBy('visa_country')
                ->orderBy('application_name')
                ->get();

            $ignoreInvoiceId = (int) $request->input('ignore_invoice_id', 0);
            $excludeApplicationIds = $ignoreInvoiceId > 0
                ? app(InvoiceItemService::class)->invoicedApplicationRecordIdsForClient(
                    (int) $subscriber->id,
                    (int) $id,
                    $ignoreInvoiceId
                )
                : [];

            return $ccService->buildInvoiceServiceTypeOptions($subscriber, $applications, $excludeApplicationIds);
        }

        // Get distinct application IDs that already exist in PaymentARs for this client
        $existingIds = PaymentARs::where('client_id', $id)
            ->distinct()
            ->pluck('application_id')
            ->toArray();

        $applications = Applications::where('client_id', $id)
            ->whereNull('assign_to')
            ->orderBy('visa_country')
            ->orderBy('application_name')
            ->get();

        $html = '<option value="">Select Application</option>';

        foreach ($applications as $app) {
            // Skip if this application already exists in PaymentARs
            if (!in_array($app->id, $existingIds)) {
                $typeName = trim((string) ($app->application_name ?? ''));
                $country = trim((string) ($app->visa_country ?? ''));
                $name = $ccService->formatApplicationServiceName($country, $typeName);
                $fee = $subscriber ? $ccService->resolveServiceFee($subscriber, $typeName, $country) : null;
                $html .= '<option value="' . e($app->application_id) . '" data-name="' . e($name) . '" data-country="' . e($country) . '" data-type="' . e($typeName) . '" data-fee="' . e($fee ?? '') . '">';
                $html .= e($name) . ' (' . e($app->application_id) . ')';
                $html .= '</option>';
            }
        }

        // Add the "Other" option at the bottom
        $html .= '<option value="Other" data-name="" data-fee="">Other</option>';

        return $html;
    }

    public function get_sub_category(Request $request)
    {
       
        $category = $request['category'];
        
            $sub_categories = Subscriber_Sub_Categories::where('status','Active')->where('category_name', '=', $category)->get();
            ?>
            <option value="">Select Sub-Category</option>
            <?php
            foreach ($sub_categories as $subcategory) {
            ?>
                <option value="<?php echo $subcategory->sub_category_name; ?>"><?php echo $subcategory->sub_category_name; ?></option>
            <?php
            }
       
    }

    public function check_user_limit(Request $request)
    {
        $subs = $request['subscriber'];
        $subscriber = User::find($subs);
        $siteusers = User::where('added_by', '=', $subscriber->id)->get();
        $offerBenefitService = app(OfferBenefitService::class);
        if ($offerBenefitService->canAddUser($subscriber)) {
            return response()->json(['limit' => 'not full']);
        }

        return response()->json(['limit' => 'full']);
    }

    public function check_client_limit(Request $request)
    {
        $subs = $request['subscriber'];
        $subscriber = User::find($subs);
        if (!$subscriber) {
            return response()->json(['limit' => 'full', 'clients' => []]);
        }

        $offerBenefitService = app(OfferBenefitService::class);
        $clientLimit = $offerBenefitService->effectiveClientLimit($subscriber);
        $clientCount = $offerBenefitService->currentClientCount($subscriber);
        $clients = Clients::whereNotNull('subscriber_id')
            ->where('subscriber_id', '=', $subscriber->id)
            ->with('subscriber')
            ->get();

        if ($clientLimit !== 'Unlimited' && $clientCount >= (int) $clientLimit) {
            return response()->json(['limit' => 'full', 'clients' => $clients]);
        }

        return response()->json(['limit' => 'not full', 'clients' => $clients]);
    }

    public function dashboard()
    {
        $user = $this->check_login();
        $accessRightsService = app(UserAccessRightsService::class);
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } elseif ($accessRightsService->userHasDashboardAccess($user)) {
            $subscriber = User::find($user->added_by);
            if (!$subscriber) {
                return redirect()->route('userprofile');
            }
        } else {
            return redirect()->route('userprofile');
        }

        // Headers and charts are whatever the subscriber configured under Settings -> Dashboard.
        $dashboardService = app(DashboardPreferenceService::class);
        $headerCards = $dashboardService->buildHeaderCards($subscriber, $user);
        $charts = $dashboardService->buildCharts($subscriber);
        $dashboardChartCount = $dashboardService->resolveChartCount($subscriber);

        $page = "dashboard";
        $activities = Activities::with('user')
            ->where('subscriber_id', '=', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('web.dashboard', compact('user', 'page', 'activities', 'headerCards', 'charts', 'dashboardChartCount'));
    }

    public function analytics()
    {
        $user = auth()->user();

        // Sidebar already controls Analytics visibility for the signed-in subscriber.
        $this->set_timezone();

        try {
            $activity = new Activities();
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Performed Analytics";
            $activity->activity_detail = "Analytics Performed by " . $user->name . " at " . date('d M, Y H:i:s');
            $activity->activity_icon = "user.png";
            $activity->save();
        } catch (\Throwable $e) {
            Log::warning('Analytics activity log failed: ' . $e->getMessage());
        }

        $subscribers = User::where('added_by', auth()->user()->id)->where('user_type', 'User')->where('name', '!=', 'ADMIN (adwiseri.com)')->pluck('id', 'name');
        $page = "analytics";
        $countries = Countries::get();
        $subscriber = app(CountryCategorySettingsService::class)->resolveSubscriber($user);
        $clientVisaChartFilters = app(\App\Services\AnalyticsClientChartService::class)
            ->visaDetailFilterAvailability((int) $subscriber->id);

        return view('web.analytics', compact('page', 'user', 'subscribers', 'countries', 'clientVisaChartFilters'));
    }
    public function client()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            if (membership_access_blocked($user)) {
                return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
            }
            $clients = Clients::withCount('dependants')->with(['applications:id,client_id,visa_country'])->where('subscriber_id', '=', $user->id)->orderBy('created_at', 'desc')->get();
        } else {
            $subscriber = User::find($user->added_by);
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            if (membership_access_blocked_for_subscriber($subscriber)) {
                return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
            }
            $clients = Clients::withCount('dependants')->with(['applications:id,client_id,visa_country'])->where('user_id', '=', $user->id)->orderBy('created_at', 'desc')->get();
        }
        $ccService = app(CountryCategorySettingsService::class);
        $subscriber = $ccService->resolveSubscriber($user);
        $countries = $ccService->resolveCountriesForDropdown($subscriber);
        $page = "clients";
        $clientVisaCountryFilters = TableFilterCountService::countBy(
            $clients,
            fn ($client) => TableFilterCountService::clientVisaCountry($client)
        );
        return view('web.client', compact('user', 'clients', 'page', 'roles','countries', 'subscriber', 'clientVisaCountryFilters'));
    }

    public function users()
    {
        $user = $this->check_login();
        // echo'<pre>';print_r($user);echo'</pre>';exit();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();

        if ($user->user_type == 'admin') {

            $siteusers = User::where('user_type', 'User')->orderBy('created_at', 'desc')->get();
        } else {
            $subscriberId = $user->user_type === 'Subscriber' ? (int) $user->id : (int) $user->added_by;
            $siteusers = User::where('added_by', '=', $subscriberId)
                ->where('user_type', 'User')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $page = "users";

        if (membership_access_blocked($user)) {
            return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
        } else {
            if (request()->ajax()) {
                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

                $siteusers = $siteusers->whereBetween('created_at', [$startDate, $endDate]);
                return DataTables::of($siteusers)
                    ->editColumn('status', function ($row) {
                        $html = '';
                        if ($row->status == 'true') {
                            $html .= '<a style="background:green;border-color:green;" href="#" onclick="userstatus(' . $row->id . ')" class="p-0 px-1">Active</a>';
                        } else {
                            $html .= '<a style="background:red;border-color:red;" href="#" onclick="userstatus(' . $row->id . ')" class="p-0 px-1">Inactive</a>';
                        }
                        return $html;
                    })
                    ->addColumn('action', function ($row) {
                        $html = '';
                        $html .= '<a href="' . route('siteuser_profile', $row->id) . '" style="text-decoration:none; background:none; border:none">';
                        $html .= '<i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i>';
                        $html .= '</a>';
                        return $html;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
            return view('web.users', compact('user', 'siteusers', 'page'));
        }
    }

    public function timezone_test()
    {
        $this->set_timezone();
        // $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        // $countries = Countries::get();
        // foreach($countries as $key => $country){
        //     echo $key+1 . " = ";
        //     echo $country->timezones;
        //     echo "<br><br>";
        // }
        // foreach($tzlist as $key => $zone){
        //     echo $key . " = " . $zone . "<br><br>";
        // }
    }

    public function add_user()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $siteusers = User::where('added_by', '=', $user->id)->get();
        $job_roles = Job_roles::where('user_id', '=', $user->id)->get();
        $offerBenefitService = app(OfferBenefitService::class);
        if ($offerBenefitService->canAddUser($user)) {
            $countries = Countries::get();
            $page = "users";
            return view('web.add_user', compact('user', 'countries', 'page', 'job_roles', 'tzlist'));
        }

        return back()->with('user_limit', 'Upgrade membership to add more users.');
    }

    public function add_client()
    {
        // $user = $this->check_login();
        $user = auth()->user();
        // Check if the user's membership has expired
        // if(membership_access_blocked($user)){
        //     return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        // }

        // Set the timezone
        $this->set_timezone();

        // Determine subscriber (either direct or through 'added_by' relationship)
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }

        // Fetch existing clients for this subscriber
        $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();

        // Fetch the user's membership plan details
        $membership_plan = Membership::where('plan_name', '=', $subscriber->membership)->first();
        $offerBenefitService = app(OfferBenefitService::class);

        if ($membership_plan && strcasecmp((string) $membership_plan->client_limit, 'Unlimited') !== 0) {
            if (!$offerBenefitService->canAddClient($subscriber)) {
                return back()->with('client_limit', 'Upgrade membership to add more clients.');
            }
        }

        // Fetch job roles based on the subscriber's user ID
        $job_roles = Job_roles::where('user_id', '=', $subscriber->id)->get();

        // Fetch available countries
        $countries = Countries::get();

        // Determine the page context
        $page = "clients";

        // Fetch client jobs based on subscriber's category and sub-category
        $ccService = app(CountryCategorySettingsService::class);
        $client_jobs = $ccService->getClientJobsForSubscriber($subscriber);

        // Return the view to add a client
        return view('web.add_client', compact('user', 'countries', 'page', 'job_roles', 'client_jobs'));
    }


    public function calculate_subscription_duration($user)
    {
        $current_date = new DateTime();
        $expiry_date = new DateTime($user->membership_expiry_date);
        $interval = $current_date->diff($expiry_date);

        return $interval->y; // Return the number of years in the subscription
    }
    public function add_new_user(request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        $data = new User();
        $this->validate(
            $request,
            [
                'name' => 'required|string|min:3|max:255',
                'phone' => 'required|phone_intl|unique:users',
                'email' => 'required|email|max:255|unique:users',
                'dob' => 'required|date',
                'designation' => 'required|string|max:255',
                'country' => 'required',
                'state' => 'required',
                'city' => 'required|string|min:3|max:255',
                'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
                'password' => 'required|string|min:8',
            ]
        );
        $country = Countries::find($request->country);
        $data->user_type = "User";
        $data->added_by = $user->id;
        $data->name = $request['name'];
        $data->phone = $request['phone'];
        $data->email = $request['email'];
        $data->dob = $request['dob'];
        $data->status = "true";
        $data->category = $user->category;
        $data->sub_category = $user->sub_category;
        $data->other_subcategory = $user->other_subcategory;
        $data->membership = $user->membership;
        $data->membership_type = $user->membership_type;
        $data->membership_start_date = $user->membership_start_date;
        $data->membership_expiry_date = $user->membership_expiry_date;
        $data->wallet = 0;
        $data->referral = $user->referral;
        $data->organization = $user->organization;
        $data->designation = $request['designation'];
        $data->employee_strength = $user->employee_strength;
        $data->country = $country->country_name;
        $data->state = $request['state'];
        $data->city = $request['city'];
        $data->pincode = $request['pincode'];
        $data->timezone = $request['timezone'];
        $crcode = $country->currency;
        $currency = Currency::where('currency_code', '=', $crcode)->first();
        if ($currency) {
            $data->currency = $currency->currency_code . "(" . $currency->currency_symbol . ")";
        } else {
            $data->currency = "USD($)";
        }
        $data->password = Hash::make($request['password']);
        // print_r($requet->$data);
        // die();
        $data->save();

        app(UserAccessRightsService::class)->applyDefaultAccessRights($data, (int) $data->added_by);

        $activity = new Activities();
        $activity->subscriber_id = $user->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "New User Added";
        $activity->activity_detail = "New user " . $request->name . " added by " . $user->name . " for " . $request->designation . " job role at " . $request->local_time;
        $activity->activity_icon = "user.png";
        $activity->local_time = $request->local_time;
        $activity->save();
        return redirect()->route('users')->with('user_added', "User added successfully.");
    }

    public function add_new_client(request $request)
    {
        // print_r($request->all());
        //  exit();
        function job_id()
        {
            $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $id = "";
            for ($i = 0; $i < 8; $i++) {
                $id = $id . $ch[rand(0, strlen($ch) - 1)];
            }
            return $id;
        }
        $user = Auth::user();
        $this->set_timezone();
        $data = new Clients();
        $this->validate(
            $request,
            [
                'name' => 'required|string|min:3|max:255',
                'phone' => 'required|phone_intl|unique:clients',
                'email' => 'required|email|max:255|unique:clients',
                'nationality' => 'required',
                'alternate_no' => 'nullable|phone_intl',
                'passport_no' => 'nullable|regex:/^[A-Z0-9]{6,14}$/',
                'country' => 'required',
                'address' => 'required|string|min:3|max:1000',
                'state' => 'required',
                'city' => 'required|string|min:3|max:255',
                'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
            ]
        );
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $data->subscriber_id = $subscriber->id;
        } else {
            $subscriber = User::find($user->added_by);
            $data->subscriber_id = $subscriber->id;
        }

        $offerBenefitService = app(OfferBenefitService::class);
        $membership_plan = Membership::where('plan_name', '=', $subscriber->membership)->first();
        if ($membership_plan && strcasecmp((string) $membership_plan->client_limit, 'Unlimited') !== 0) {
            if (!$offerBenefitService->canAddClient($subscriber)) {
                return back()->with('client_limit', 'Upgrade membership to add more clients.');
            }
        }

        $country = Countries::find($request->country);
        $nationality = Countries::find($request->nationality);
        $data->user_id = $user->id;
        $data->name = $request['name'];
        $data->phone = $request['phone'];
        $data->email = $request['email'];
        $data->alternate_no = $request['alternate_no'];
        $data->nationality = $nationality->country_name;
        $data->passport_no = $request['passport_no'];
        $data->dob = $request['dob'];
        $data->address = $request['address'];
        $data->country = $country->country_name;
        $data->state = $request['state'];
        $data->city = $request['city'];
        $data->pincode = $request['pincode'];
        $data->save();
        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "New Client Added";
        if ($user->user_type == "Subscriber") {
            $activity->activity_detail = "New client " . $request->name . " added by " . $user->name . " for " . $request->job_role . " job at " . $request->local_time;
        } else {
            $activity->activity_detail = "New client " . $request->name . " added by " . $user->name . "(" . $subscriber->name . ") for " . $request->job_role . " job at " . $request->local_time;
        }
        $activity->activity_icon = "user.png";
        $activity->local_time = $request->local_time;
        $activity->save();
        // $application = new Applications();
        // $application->client_id = $data->id;
        // $application->subscriber_id = $subscriber->id;
        // $application->application_id = job_id();
        // $application->application_category = $subscriber->category;
        // $application->application_subcategory = $subscriber->sub_category;
        // $application->application_name = $request['job_role'];
        // $application->application_country = $request['visa_country'];
        // $application->application_detail = $request['job_detail'];
        // $application->application_program = $request['study_program'];
        // $application->application_status = $request['job_status'];
        // $application->start_date = $request['job_open_date'];
        // $application->end_date = $request['job_completion_date'];
        // $application->save();
        // $activity = new Activities();
        // $activity->subscriber_id = $subscriber->id;
        // $activity->user_id = $user->id;
        // $activity->user_name = $user->name;
        // $activity->activity_name = "New Application Added";
        // if ($user->user_type == "Subscriber") {
        //     $activity->activity_detail = "New Application of " . $request->job_role . " added by " . $user->name . " at " . $request->local_time;
        // } else {
        //     $activity->activity_detail = "New Application of " . $request->job_role . " added by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
        // }
        // $activity->activity_icon = "user.png";
        // $activity->local_time = $request->local_time;
        // $activity->save();
        return redirect()->route('client')->with('client_added', "Client added successfully.");
    }

    public function update_user(request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($request->filled('id') && (int) $request->id !== (int) $user->id) {
                return $this->update_siteuser($request);
            }

            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $user_update = Auth::user();
            if (isset($request->moredetails)) {
                $this->validate(
                    $request,
                    [
                        'organization' => 'required|string|max:255',
                        'designation' => 'required|string|max:255',
                        'employee_strength' => 'required|string|max:255',
                        'address_line' => 'required|string|max:255',
                        'country' => 'required',
                        'state' => 'required',
                        'city' => 'required|string|max:255',
                        'pincode' => 'required|string',
                    ]
                );
                $country = Countries::find($request->country);
                $user_update->organization = $request['organization'];
                $user_update->designation = $request['designation'];
                $user_update->employee_strength = $request['employee_strength'];
                $user_update->address_line = $request['address_line'];
                $user_update->website = trim((string) ($request['website'] ?? ''));
                $user_update->country = $country->country_name;
                $user_update->state = $request['state'];
                $user_update->city = $request['city'];
                $user_update->pincode = $request['pincode'];
                $user_update->timezone = $request['timezone'];
                $crcode = $country->currency;
                $currency = Currency::where('currency_code', '=', $crcode)->first();
                if ($currency) {
                    $user_update->currency = $currency->currency_code . "(" . $currency->currency_symbol . ")";
                } else {
                    $user_update->currency = "USD($)";
                }
                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") Updates his profile at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return redirect()->route('login');
            } elseif (isset($request->profile)) {
                $country = Countries::find($request->country);
                $user_update->name = $request['name'];
                $user_update->phone = $request['phone'];
                $user_update->organization = $request['organization'];
                $user_update->designation = $request['designation'];
                $user_update->employee_strength = $request['employee_strength'];
                $user_update->address_line = $request['address_line'];
                $user_update->website = trim((string) ($request['website'] ?? ''));
                $user_update->country = $country->country_name;
                $user_update->state = $request['state'];
                $user_update->city = $request['city'];
                $user_update->pincode = $request['pincode'];
                $user_update->timezone = $request['timezone'];
                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") Updates his profile at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('success', 'Profile updated successfully.');
            } elseif (isset($request->profile_image)) {
                if ($request->hasFile('profile_img')) {
                    $file = $request->file('profile_img');
                    $extension = $file->getClientOriginalName();
                    $filename = time() . $extension;
                    $file->move('web_assets/users/user' . $user_update->id . '/', $filename);
                    $user_update->profile_img = $filename;
                }
                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") Updates his profile at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('success', 'Profile updated successfully.');
            } elseif (isset($request->logo_image)) {
                if ($request->hasFile('organization_logo')) {
                    $file = $request->file('organization_logo');
                    $extension = $file->getClientOriginalName();
                    $filename = time() . $extension;
                    $file->move('web_assets/users/user' . $user_update->id . '/', $filename);
                    $user_update->organization_logo = $filename;
                }
                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Organization Logo Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates organization logo at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") Updates organization logo at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('logo_updated', 'Logo updated successfully.');
            }
        } else {
            return redirect()->route('login');
        }
    }
    public function update_user_affiliate(request $request)
    {

        $user = auth()->guard('affiliates')->user();
        $affiliateUser = User::where('email', $user->email)->first();
        $this->set_timezone();
        if ($user) {

            $user_update = $user;
            User::where('email', $user->email)->update(
                [
                    'timezone' => $request->timezone,
                ]
            );
            if (isset($request->moredetails)) {
                $this->validate(
                    $request,
                    [
                        'organization' => 'required|string|max:255',
                        'designation' => 'required|string|max:255',
                        'employee_strength' => 'required|string|max:255',
                        'address_line' => 'required|string|max:255',
                        'country' => 'required',
                        'state' => 'required',
                        'city' => 'required|string|max:255',
                        'pincode' => 'required|string',
                    ]
                );
                $country = Countries::find($request->country);
                $user_update->organization = $request['organization'];
                $user_update->designation = $request['designation'];
                $user_update->employee_strength = $request['employee_strength'];
                $user_update->address_line = $request['address_line'];
                $user_update->website = trim((string) ($request['website'] ?? ''));
                $user_update->country = $country->country_name;
                $user_update->state = $request['state'];
                $user_update->city = $request['city'];
                $user_update->pincode = $request['pincode'];
                $user_update->timezone = $request['timezone'];
                $crcode = $country->currency;
                $currency = Currency::where('currency_code', '=', $crcode)->first();
                if ($currency) {
                    $user_update->currency = $currency->currency_code . "(" . $currency->currency_symbol . ")";
                } else {
                    $user_update->currency = "USD($)";
                }
                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") Updates his profile at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return redirect()->route('login');
            } elseif (isset($request->profile)) {
                $country = Countries::find($request->country);
                $user_update->name = $request['name'];
                $user_update->email = $request['email'];
                $user_update->phone = $request['phone'];

                $user_update->country = $country->country_name;
                $user_update->city = $request['city'];

                $user_update->save();
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;


                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('success', 'Profile updated successfully.');
            } elseif (isset($request->profile_image)) {
                if ($request->hasFile('profile_img')) {
                    $file = $request->file('profile_img');
                    $extension = $file->getClientOriginalName();
                    $filename = time() . $extension;
                    $file->move('web_assets/users/user' . $affiliateUser->id . '/', $filename);
                    $affiliateUser->profile_img = $filename;
                }
                $affiliateUser->save();
                $activity = new Activities();
                // $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Profile Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "" . $user->name . " Updates his profile at " . $request->local_time;
                } else {
                    $activity->activity_detail = "" . $user->name . "(" . $affiliateUser->name . ") Updates his profile at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('success', 'Profile updated successfully.');
            } elseif (isset($request->logo_image)) {
                if ($request->hasFile('organization_logo')) {
                    $file = $request->file('organization_logo');
                    $extension = $file->getClientOriginalName();
                    $filename = time() . $extension;
                    $file->move('web_assets/users/user' . $affiliateUser->id . '/', $filename);
                    $affiliateUser->organization_logo = $filename;
                }
                $affiliateUser->save();
                $activity = new Activities();
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Organization Logo Updated";
                $activity->activity_detail = "" . $user->name . "(" . $affiliateUser->name . ") Updates organization logo at " . $request->local_time;
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('logo_updated', 'Logo updated successfully.');
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function update_siteuser(request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }

        $siteuser_update = User::find($request->id);
        if (!$siteuser_update || $siteuser_update->user_type !== 'User') {
            return redirect()->route('users')->with('error', 'Staff user not found.');
        }

        if ($user->user_type !== 'admin' && (!$subscriber || (int) $siteuser_update->added_by !== (int) $subscriber->id)) {
            abort(403);
        }

        $activitySubscriberId = $user->user_type === 'admin'
            ? (int) $siteuser_update->added_by
            : (int) $subscriber->id;

        $staffProfileRedirect = route('siteuser_profile', ['id' => $siteuser_update->id, 'edit' => 1]);

        $isStaffProfileUpdate = $request->has('staff_profile') || $request->has('profile');
        $isStaffImageUpdate = $request->has('staff_profile_image') || $request->has('profile_image');

        if ($isStaffProfileUpdate) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'phone' => 'required|phone_intl',
                'dob' => 'required|date',
                'designation' => 'required|string|max:255',
                'country' => 'required',
                'state' => 'required',
                'city' => 'required|string|min:3|max:255',
                'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
                'timezone' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->to($staffProfileRedirect)
                    ->withErrors($validator)
                    ->withInput();
            }

            $country = Countries::find($request->country);
            if (!$country) {
                return redirect()->to($staffProfileRedirect)
                    ->withErrors(['country' => 'Please select a valid country.'])
                    ->withInput();
            }

            $siteuser_update->name = $request['name'];
            $siteuser_update->phone = $request['phone'];
            $siteuser_update->dob = $request['dob'];
            $siteuser_update->designation = $request['designation'];
            $siteuser_update->country = $country->country_name;
            $siteuser_update->state = $request['state'];
            $siteuser_update->city = $request['city'];
            $siteuser_update->pincode = $request['pincode'];
            $siteuser_update->timezone = $request['timezone'];
            $siteuser_update->save();

            $activity = new Activities();
            $activity->subscriber_id = $activitySubscriberId;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "User Profile Updated";
            $activity->activity_detail = "" . $user->name . " Updated profile of his staff " . $siteuser_update->name . " at " . $request->local_time;
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();

            return redirect()->route('siteuser_profile', $siteuser_update->id)->with('success', 'Staff profile updated successfully.');
        }

        if ($isStaffImageUpdate) {
            if ($request->hasFile('profile_img')) {
                $file = $request->file('profile_img');
                $extension = $file->getClientOriginalName();
                $filename = time() . $extension;
                $file->move('web_assets/users/user' . $siteuser_update->id . '/', $filename);
                $siteuser_update->profile_img = $filename;
            }
            $siteuser_update->save();

            $activity = new Activities();
            $activity->subscriber_id = $activitySubscriberId;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "User Profile Updated";
            $activity->activity_detail = "" . $user->name . " Updated profile of his staff " . $siteuser_update->name . " at " . $request->local_time;
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();

            return redirect()->route('siteuser_profile', $siteuser_update->id)->with('success', 'Staff profile image updated successfully.');
        }

        return redirect()->route('siteuser_profile', $siteuser_update->id);
    }

    public function change_password(Request $request)
    {
        $user = Auth::user();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
        $old_password = $request->old_password;
        if (password_verify($old_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "User Changed his Password";
            if ($user->user_type == "Subscriber") {
                $activity->activity_detail = "" . $user->name . " changed his password at " . $request->local_time;
            } else {
                $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") changed his password at " . $request->local_time;
            }
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            Auth::logout();
            Session::flush();
            return redirect()->route('login')->with('password_changed', 'Password changed successfully.');
        } else {
            return back()->with('wrong_password', 'The old password is incorrect.');
        }
    }
    public function change_password_affiliate(Request $request)
    {
        $user = auth()->guard('affiliates')->user();
        $subscriber = $user;

        $old_password = $request->old_password;
        if (password_verify($old_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "User Changed his Password";
            if ($user->user_type == "Affiliate") {
                $activity->activity_detail = "" . $user->name . " changed his password at " . $request->local_time;
            } else {
                $activity->activity_detail = "" . $user->name . "(" . $subscriber->name . ") changed his password at " . $request->local_time;
            }
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            Auth::logout();
            Session::flush();
            return redirect()->route('Affiliates.create')->with('password_changed', 'Password changed successfully.');
        } else {
            return back()->with('wrong_password', 'The old password is incorrect.');
        }
    }

    public function update_client(request $request)
    {
        $user = Auth::user();
        $this->set_timezone();

        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $client = Clients::find($request->id);
            if ($client) {
                $client_update = Clients::find($request->id);
                if (isset($request->profile)) {
                    $request->validate([
                        'name' => 'required|string|min:3|max:255',
                        'phone' => 'required|phone_intl',
                        'email' => 'required|email|max:255',
                        'alternate_no' => 'nullable|phone_intl',
                        'passport_no' => 'nullable|regex:/^[A-Z0-9]{6,14}$/',
                        'country' => 'required',
                        'nationality' => 'required',
                        'address' => 'required|string|min:3|max:1000',
                        'state' => 'required',
                        'city' => 'required|string|min:3|max:255',
                        'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
                    ]);

                    $country = Countries::find($request->country);
                    $nationality = Countries::find($request->nationality);
                    $client_update->name = $request['name'];
                    $client_update->phone = $request['phone'];
                    $client_update->email = $request['email'];
                    $client_update->alternate_no = $request['alternate_no'];
                    $client_update->nationality = $nationality->country_name;
                    $client_update->passport_no = $request['passport_no'];
                    $client_update->dob = $request['dob'];
                    $client_update->address = $request['address'];
                    $client_update->country = $country->country_name;
                    $client_update->state = $request['state'];
                    $client_update->city = $request['city'];
                    $client_update->pincode = $request['pincode'];
                    $client_update->save();
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->client_id = $request->id;
                    $activity->activity_name = "Client Profile Updated";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = $user->name . " Updates client " . $client->name . " profile at " . $request->local_time;
                    } else {
                        $activity->activity_detail = $user->name . "(" . $subscriber->name . ") Updates client " . $client->name . " profile at " . $request->local_time;
                    }
                    $activity->activity_icon = "user.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();
                    return back()->with('success', 'Profile updated successfully.');
                } elseif (isset($request->profile_image)) {
                    if ($request->hasFile('profile_img')) {
                        $file = $request->file('profile_img');
                        $extension = $file->getClientOriginalName();
                        $filename = time() . $extension;
                        $file->move('web_assets/users/client' . $client_update->id . '/', $filename);
                        $client_update->profile_img = $filename;
                    }
                    $client_update->save();
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->client_id = $request->id;
                    $activity->activity_name = "Client Profile Updated";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = $user->name . " Updates client " . $client->name . " profile at " . $request->local_time;
                    } else {
                        $activity->activity_detail = $user->name . "(" . $subscriber->name . ") Updates client " . $client->name . " profile at " . $request->local_time;
                    }
                    $activity->activity_icon = "user.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();
                    return back()->with('success', 'Profile updated successfully.');
                } elseif (isset($request->job)) {
                    $client_update->job_id = $request['job_id'];
                    $client_update->job_detail = $request['job_detail'];
                    $client_update->job_open_date = $request['job_open_date'];
                    $client_update->job_status = $request['job_status'];
                    $client_update->job_completion_date = $request['job_completion_date'];
                    $client_update->save();
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->client_id = $request->id;
                    $activity->activity_name = "Client Job Details Updated";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = $user->name . " Updates client " . $client->name . " job details at " . $request->local_time;
                    } else {
                        $activity->activity_detail = $user->name . "(" . $subscriber->name . ") Updates client " . $client->name . " job details at " . $request->local_time;
                    }
                    $activity->activity_icon = "job_icon.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();
                    return back()->with('success', 'Profile updated successfully.');
                }
            } else {
                return back();
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function userprofile()
    {
        // echo'<pre>';print_r(auth()->user());echo'</pre>';exit();
        $user = $this->check_login();
        if ($user->type_user != "affiliate" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $countries = Countries::all();
        $states = [];
        foreach ($countries as $country) {
            if ($country->country_name == $user->country) {
                $states = States::where('country_id', '=', $country->id)->get();
            }
        }
        $page = "profile";
        if ($user->user_type == 'Affiliate') {
            $user = auth()->guard('affiliates')->user();

            return view('affiliate.userprofile', compact('user', 'countries', 'states', 'page', 'tzlist'));
        }
        return view('web.userprofile', compact('user', 'countries', 'states', 'page', 'tzlist'));
    }
    public function userprofile_affiliate()
    {

        $affiliateUser = auth()->guard('affiliates')->user();
        if (!isset($affiliateUser)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $countries = Countries::all();
        $user = User::where('email', $affiliateUser->email)->first();
        $page = "profile";

        return view('affiliate.userprofile', compact('affiliateUser', 'user', 'countries', 'page', 'tzlist'));
    }

    public function siteuser_profile($id = null)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if (!empty($id)) {
            $siteuser = User::find($id);
            if (!$siteuser || $siteuser->user_type !== 'User') {
                return redirect()->route('users')->with('error', 'Staff user not found.');
            }

            if ($user->user_type !== 'admin') {
                $subscriberId = $user->user_type === 'Subscriber' ? $user->id : (int) $user->added_by;
                if ((int) $siteuser->added_by !== $subscriberId) {
                    abort(403);
                }
            }

            $this->set_timezone();
            $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            $countries = Countries::get();
            $states = collect();
            foreach ($countries as $country) {
                if ($country->country_name == $siteuser->country) {
                    $states = States::where('country_id', '=', $country->id)->get();
                    break;
                }
            }
            $page = "users";
            return view('web.siteuser_profile', compact('siteuser', 'user', 'countries', 'states', 'page', 'tzlist'));
        } else { //view the page.
            return back();
        }
    }

    public function client_profile($id = null)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if (!empty($id)) { //edit the page.
            $client  = Clients::find($id);
            $this->set_timezone();
            $countries = Countries::get();
            foreach ($countries as $country) {
                if ($country->country_name == $client->country) {
                    $states = States::where('country_id', '=', $country->id)->get();
                }
            }
            // $states = States::get();
            $page = "clients";
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            $documents = Client_Docs::where('client_id', '=', $id)->get();
            $visibility = app(ApplicationVisibilityService::class);
            $applications = $visibility->queryForUser($user, User::find($client->subscriber_id))
                ->where('client_id', '=', $client->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $messages = Messages::where('client_id', '=', $client->id)->orderBy('created_at', 'desc')->get();
            $activities = Activities::where('client_id', '=', $client->id)->orderBy('created_at', 'desc')->get();
            return view('web.client_profile', compact('client', 'user', 'countries', 'states', 'page', 'documents', 'activities', 'messages', 'applications', 'roles'));
        } else { //view the page.
            return back();
        }
    }

    public function generate_client_care_letter(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'letter_type' => 'required|in:oisc_iaa,service_agreement',
            'application_type' => 'required|string|min:3|max:150',
            'application_name' => 'nullable|string|max:150',
            'consultation_date' => 'required|date',
            'immigration_status' => 'nullable|string|max:255',
            'client_instructions' => 'nullable|string|max:4000',
            'advice_given' => 'nullable|string|max:4000',
            'work_agreed' => 'nullable|string|max:4000',
            'estimated_timeline' => 'required|string|min:2|max:150',
            'key_dates' => 'nullable|string|max:1000',
            'fee_details' => 'nullable|string|max:1200',
            'fixed_fee' => 'nullable|string|max:100',
            'home_office_fee' => 'nullable|string|max:100',
            'ihs_fee' => 'nullable|string|max:100',
            'additional_costs' => 'nullable|string|max:1200',
            'vat_note' => 'nullable|string|max:255',
            'merits_of_case' => 'required|integer|min:0|max:100',
            'case_notes' => 'nullable|string|max:1500',
            'line_manager_name' => 'nullable|string|max:150',
            'line_manager_phone' => 'nullable|phone_intl',
            'line_manager_email' => 'nullable|email|max:150',
            'office_hours' => 'nullable|string|max:150',
            'complaint_handling_details' => 'nullable|string|max:1500',
            'oisc_registration_number' => 'nullable|string|max:100',
            'authorisation_level' => 'nullable|string|max:150',
            'allow_resend' => 'nullable|in:0,1',
            'correction_note' => 'nullable|string|max:500',
        ]);

        $client = Clients::findOrFail($validated['client_id']);

        $baseDocName = $validated['letter_type'] === 'oisc_iaa' ? 'Client Care Letter' : 'Service Agreement';

        $existingLetter = Client_Docs::where('client_id', $client->id)
            ->where('doc_name', 'like', $baseDocName . '%')
            ->orderByDesc('id')
            ->first();

        $allowResend = (int) ($validated['allow_resend'] ?? 0) === 1;

        if ($existingLetter && !$allowResend) {
            return back()->with('ccl_exists', $baseDocName . ' has already been sent for this client. Use resend only if details were incorrect.');
        }

        if ($existingLetter && $allowResend && empty($validated['correction_note'])) {
            return back()->withErrors(['correction_note' => 'Please add a correction note before re-sending the document.'])->withInput();
        }

        $letterData = [
            'client' => $client,
            'subscriber' => $subscriber,
            'prepared_by' => $user,
            'letter_type' => $validated['letter_type'],
            'document_title' => $baseDocName,
            'reference_no' => 'IMM/' . now()->format('ymd') . '/' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $user->name), 0, 3)),
            'issue_date' => now()->format('d-m-Y'),
            'consultation_date' => date('d F Y', strtotime($validated['consultation_date'])),
            'application_type' => $validated['application_type'],
            'application_name' => $validated['application_name'] ?? '-',
            'immigration_status' => $validated['immigration_status'] ?? 'As stated during consultation and based on documents shared.',
            'client_instructions' => $validated['client_instructions'] ?? 'As discussed with the adviser during initial consultation.',
            'advice_given' => $validated['advice_given'] ?? 'Advice provided based on information and documents shared by the client.',
            'work_agreed' => $validated['work_agreed'] ?? 'Preparation, review and submission support for the identified application.',
            'estimated_timeline' => $validated['estimated_timeline'],
            'key_dates' => $validated['key_dates'] ?? 'Key dates will be tracked and communicated in writing as the matter progresses.',
            'fee_details' => $validated['fee_details'] ?? 'Fees discussed during consultation and confirmed in writing.',
            'fixed_fee' => $validated['fixed_fee'] ?? '0',
            'home_office_fee' => $validated['home_office_fee'] ?? '0',
            'ihs_fee' => $validated['ihs_fee'] ?? '0',
            'additional_costs' => $validated['additional_costs'] ?? 'Additional costs may include translation, interpreter, courier and photocopying expenses.',
            'vat_note' => $validated['vat_note'] ?? 'No VAT will be charged unless otherwise stated in writing.',
            'merits_of_case' => $validated['merits_of_case'],
            'case_notes' => $validated['case_notes'] ?? '',
            'adviser_name' => $user->name,
            'adviser_phone' => $user->phone ?? '-',
            'adviser_email' => $user->email,
            'line_manager_name' => $validated['line_manager_name'] ?? 'N/A',
            'line_manager_phone' => $validated['line_manager_phone'] ?? '-',
            'line_manager_email' => $validated['line_manager_email'] ?? '-',
            'organisation_name' => $subscriber->organization ?: $subscriber->name,
            'organisation_address' => $subscriber->address ?: 'Address available on request.',
            'organisation_phone' => $subscriber->phone ?: '-',
            'organisation_email' => $subscriber->email,
            'office_hours' => $validated['office_hours'] ?? '9am to 5pm during weekdays',
            'complaint_handling_details' => $validated['complaint_handling_details'] ?? 'Please raise concerns first with your case adviser or their line manager in writing.',
            'oisc_registration_number' => $validated['oisc_registration_number'] ?? 'To be provided by organisation',
            'authorisation_level' => $validated['authorisation_level'] ?? 'Level 1',
            'correction_note' => $validated['correction_note'] ?? null,
        ];

        $pdf = Pdf::loadView('web.client_care_letter_pdf', $letterData)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $folder = public_path('web_assets/users/client' . $client->id . '/docs/');
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $fileName = strtolower(str_replace(' ', '-', $baseDocName)) . '-' . $client->id . '-' . time() . '.pdf';
        file_put_contents($folder . $fileName, $pdf->output());
        // echo $fileName;exit();
        $document = new Client_Docs();
        $document->client_id = $client->id;
        $document->user_id = $user->id;
        $document->doc_name = $existingLetter && $allowResend ? $baseDocName . ' (Corrected)' : $baseDocName;
        $document->doc_file = $fileName;
        $document->save();

        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->client_id = $client->id;
        $activity->activity_name = $existingLetter && $allowResend ? $baseDocName . ' Re-Sent' : $baseDocName . ' Sent';
        $activity->activity_detail = $user->name . ' generated and emailed ' . $baseDocName . ' for ' . $client->name . ' at ' . ($validated['local_time'] ?? now()->format('d M, Y H:i:s'));
        $activity->activity_icon = 'doc.png';
        $activity->local_time = $validated['local_time'] ?? null;
        $activity->save();

        try {
            Mail::to($client->email)->send(new ClientCareLetterMail($letterData, $folder . $fileName));
            return back()->with('ccl_sent', $baseDocName . ' generated, saved to documents, and emailed to the client for signature.');
        } catch (\Exception $exception) {
            echo'<pre>';print_r($exception);exit();
            Log::error('Client care letter email sending failed.', [
                'client_id' => $client->id,
                'client_email' => $client->email,
                'document' => $fileName,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('ccl_error', $baseDocName . ' PDF was generated and saved, but email delivery failed. Please check email settings and try resend.');
        }
    }

    public function upload_client_doc(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
        $document = Client_Docs::find($request->id);
        $docFileRule = $document ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096' : 'required|file|mimes:jpg,jpeg,png,pdf|max:4096';
        $this->validate($request, [
            'doc_name' => 'required|string|min:3|max:100',
            'doc_file' => $docFileRule,
        ], [
            'doc_file.mimes' => 'Please select a valid file format (jpg, jpeg, png, pdf).',
            'doc_file.max' => 'Please select file up to 4MB.',
        ]);
        if ($document) {
            $document->doc_name = $request['doc_name'];
            if ($request->hasFile('doc_file')) {
                $file = $request->file('doc_file');
                $filename = \App\Support\DocumentFileName::storageName($request->doc_name, $file->getClientOriginalName());
                $file->move('web_assets/users/client' . $document->client_id . '/docs/', $filename);
                $document->doc_file = $filename;
                $document->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->client_id = $request->client_id;
                $activity->activity_name = "Client Document Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = $user->name . " Updates " . $client->name . " " . $request->doc_name . " Document at " . $request->local_time;
                } else {
                    $activity->activity_detail = $user->name . "(" . $subscriber->name . ") Updates " . $client->name . " " . $request->doc_name . " Document at " . $request->local_time;
                }
                $activity->activity_icon = "doc.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('updated', 'Updated successfully.');
            }
        } else {
            $doc = new Client_Docs();
            $client = Clients::find($request->client_id);
            $doc->client_id = $request['client_id'];
            $doc->user_id = $user->id;
            $doc->doc_name = $request['doc_name'];
            if ($request->hasFile('doc_file')) {
                $file = $request->file('doc_file');
                $filename = \App\Support\DocumentFileName::storageName($request->doc_name, $file->getClientOriginalName());
                $file->move('web_assets/users/client' . $doc->client_id . '/docs/', $filename);
                $doc->doc_file = $filename;
            }

            $doc->save();
            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->client_id = $request->client_id;
            $activity->activity_name = "New Client Document Added";
            if ($user->user_type == "Subscriber") {
                $activity->activity_detail = $user->name . " Uploads " . $client->name . " " . $request->doc_name . " Document at " . $request->local_time;
            } else {
                $activity->activity_detail = $user->name . "(" . $subscriber->name . ") Uploads " . $client->name . " " . $request->doc_name . " Document at " . $request->local_time;
            }
            $activity->activity_icon = "doc.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            return back()->with('uploaded', 'Uploaded successfully.');
        }
    }

    public function delete_siteuser($id = null, $localtime = null)
    {
        if (!empty($id)) { //edit the page.
            $user = Auth::user();
            $this->set_timezone();
            if ($user) {
                $subscriber = $user;
                $siteuser = User::find($id);
                $username = $siteuser->name;
                $siteuser->delete();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "User Deleted";
                $activity->activity_detail = $user->name . " deleted staff user" . $username . " account at " . $localtime;
                $activity->activity_icon = "user.png";
                $activity->local_time = $localtime;
                $activity->save();
                return back()->with('deleted', 'User deleted successfully.');
            } else {
                return redirect()->route('login');
            }
        } else { //view the page.
            return back();
        }
    }

    public function delete_client($id = null, $localtime = null)
    {
        if (!empty($id)) { //edit the page.
            $user = Auth::user();
            $this->set_timezone();
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if ($user) {
                $client = Clients::find($id);
                $client->delete();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->client_id = $id;
                $activity->activity_name = "Client Deleted";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = $user->name . " deleted " . $client->name . " account at " . $localtime;
                } else {
                    $activity->activity_detail = $user->name . "(" . $subscriber->name . ") deleted " . $client->name . " account at " . $localtime;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $localtime;
                $activity->save();
                return back()->with('deleted', 'Client deleted successfully.');
            } else {
                return redirect()->route('login');
            }
        } else { //view the page.
            return back();
        }
    }

    public function applications()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $this->set_timezone();
        if ($user) {
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            $visibility = app(ApplicationVisibilityService::class);

            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } elseif ($user->user_type == "admin") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }

            $applications = $visibility->queryForUser($user, $subscriber)
                ->orderBy('created_at', 'desc')
                ->get();
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            $page = "applications";


            if (request()->ajax()) {
                $application_roles = null;
                if ($user->user_type != 'admin') {
                    $application_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Applications')->first();
                }


                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();


                $applications = $applications->whereBetween('created_at', [$startDate, $endDate]);
                return DataTables::of($applications)
                ->addIndexColumn()
                ->editColumn('client_name', function ($row) {
                    return $row->client ?  $row->client->name.'('.$row->client_id.')' :'';
                })
                    ->editColumn('application_name', function ($row) {
                        return $row->application_name.'('.$row->application_id.')';
                    })
                    ->editColumn('end_date', function ($row) {
                        if ($row->end_date != null) {
                            return date("d-m-Y", strtotime($row->end_date));
                        }
                    })
                    ->editColumn('start_date', function ($row) {
                        return date("d-m-Y", strtotime($row->start_date));
                    })
                    ->addColumn('action', function ($row) use ($application_roles, $user) {
                        $html = '';
                        $html .= '<a style="background:transparent;border:none;" class="p-0 m-0 text-dark" ';
                        if ($user->user_type == 'admin' || $application_roles->read_only == 1 || $application_roles->read_write_only == 1) {
                            $html .= 'href="' . route('view_application', $row->id) . '">';
                        } else {
                            $html .= 'href="#">';
                        }
                        $html .= '<i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a>';

                        // $html .= '<a style="background:transparent;border:none;" class="p-0 m-0 text-dark" ';
                        // if ($application_roles->update_only == 1) {
                        //     $html .= 'href="' . route('update_application', $row->id) . '">';
                        // } else {
                        //     $html .= 'href="#">';
                        // }
                        // $html .= '<i class="fa-solid fa-edit btn text-primary p-1 m-0"></i></a>';

                        // $html .= '<i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" ';
                        // if ($application_roles->delete_only == 1) {
                        //     $html .= 'onclick="deleteapplication(' . $row->id . ')"></i>';
                        // } else {
                        //     $html .= '></i>';
                        // }
                        return $html;
                    })
                    ->rawcolumns(['application_name', 'action'])
                    ->make(true);
            }

            $applicationTypeFilters = TableFilterCountService::countBy(
                $applications,
                fn ($application) => $application->application_name
            );
            return view('web.applications', compact('applications', 'clients', 'user', 'page', 'roles', 'applicationTypeFilters'));
        } else {
            return redirect()->route('login');
        }
    }

    public function user_application_tracking(){

        $user = Auth::user();
        $clients = Clients::where('subscriber_id', '=', $user->id)->get();
        // $subscribers = User::where('user_type', '=', 'Subscriber')->get();
        if ($user) {
            $visibility = app(ApplicationVisibilityService::class);

            if ($user->user_type == "Subscriber" || $user->user_type == "admin") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            $applications = $visibility->queryForUser($user, $subscriber)->get();
        } else {
            $applications = collect();
        }
        $countries = Countries::get();
        $page = "applications";
        return view('web.application_tracking', compact('clients','applications', 'user', 'page', 'countries'));
        
    }

    public function getClientsBySubscriber()
    {   
        $user = Auth::user();
        if ($user->user_type == "Subscriber" || $user->user_type == "admin") {
            $subscriberId = $user->id;
        } else {
            $subscriberId = User::find($user->added_by)->id;
        }
        $clients = Clients::where('subscriber_id', $subscriberId)
            ->whereHas('applications')
            ->get();
        return response()->json($clients);
    }

    public function getApplicationsByClient($clientId)
    {
        $user = Auth::user();
        $client = Clients::find($clientId);
        if (!$client) {
            return response()->json([]);
        }

        $visibility = app(ApplicationVisibilityService::class);
        $applications = $visibility->queryForUser($user, User::find($client->subscriber_id))
            ->where('client_id', $clientId)
            ->get(['id', 'application_name']);

        return response()->json($applications);
    }

    public function getApplicationData($id)
    {
        $user = Auth::user();
        $application = Applications::with('client.user')->find($id);

        if (!$application || !app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return response()->json([]);
        }

        $timeline = ApplicationStatusTrack::where('application_id', $application->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($track, $idx) {
                $changedAt = $track->changed_at ? Carbon::parse($track->changed_at) : ($track->created_at ? $track->created_at->copy() : null);

                return [
                    'index' => $idx + 1,
                    'status' => $track->status,
                    'start_date' => $changedAt ? $changedAt->format('d/m/Y') : '--',
                    'end_date' => $changedAt ? $changedAt->format('d/m/Y') : '--',
                    'user' => $track->updated_by_name ?: '--',
                ];
            })
            ->values();

        if ($timeline->isEmpty()) {
            $clientUser = $application->client && $application->client->user
                ? $application->client->user->name . ' (' . $application->client->user->id . ')'
                : '--';

            $timeline = collect([[
                'index' => 1,
                'status' => $application->application_status ?: 'Client Registered',
                'start_date' => $this->formatApplicationTrackingDate($application->start_date),
                'end_date' => $this->formatApplicationTrackingDate($application->end_date),
                'user' => $clientUser,
            ]]);
        }

        return response()->json($timeline);
    }

    private function formatApplicationTrackingDate($value): string
    {
        if (!$value || trim((string) $value) === '') {
            return '--';
        }

        $value = trim((string) $value);
        foreach (['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('d/m/Y');
            } catch (\Exception $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return '--';
        }
    }

    public function updateApplicationStatus(Request $request)
    {
        $request->validate([
            'application_id' => 'required|integer|exists:applications,id',
            'status' => 'required|string|max:255',
        ]);

        $application = Applications::findOrFail($request->application_id);

        $user = Auth::user();
        if (!app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return response()->json(['message' => 'You do not have access to this application.'], 403);
        }

        $currentStatus = $application->application_status ?: 'Client Registered';
        if ($currentStatus === 'Apointment Booked') {
            $currentStatus = 'Appointment Booked';
        }
        $newStatus = $request->status;
        if ($newStatus === 'Apointment Booked') {
            $newStatus = 'Appointment Booked';
        }

        $statusFlow = self::APPLICATION_STATUS_FLOW;
        $currentIndex = array_search($currentStatus, $statusFlow, true);
        $newIndex = array_search($newStatus, $statusFlow, true);

        if ($newIndex === false) {
            return response()->json(['message' => 'Invalid status selected.'], 422);
        }

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        if ($newIndex < $currentIndex) {
            return response()->json(['message' => 'Status cannot move backwards.'], 422);
        }

        if ($newStatus === $currentStatus) {
            return response()->json(['message' => 'Status already set.']);
        }

        $application->application_status = $newStatus;
        $application->save();

        ApplicationStatusTrack::create([
            'application_id' => $application->id,
            'status' => $newStatus,
            'updated_by' => $user ? $user->id : null,
            'updated_by_name' => $user ? ($user->name . ' (' . $user->id . ')') : null,
            'changed_at' => now(),
        ]);

        app(\App\Services\OperationalNotificationService::class)
            ->notifyApplicationClosure($application, $newStatus);

        return response()->json(['message' => 'Application status updated successfully.']);
    }

    public function add_application()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
        $ccService = app(CountryCategorySettingsService::class);
        $client_jobs = $ccService->getClientJobsForSubscriber($subscriber);
        $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        if ($redirect = \App\Support\NoClientGuard::redirectIfNoClients($user)) {
            return $redirect;
        }
        $countries = $ccService->resolveCountriesForDropdown($subscriber);
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $page = "applications";
        return view('web.add_application', compact('clients', 'user', 'page', 'countries', 'client_jobs', 'subscriber', 'visaCategories'));
    }

    public function update_application($id)
    {
        $user = Auth::user();
        $this->set_timezone();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $application = Applications::find($id);
        if (!$application || !app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return redirect()->route('applications');
        }

        $client = Clients::find($application->client_id);
        $subscriber = User::find($client->subscriber_id);
        $ccService = app(CountryCategorySettingsService::class);
        $countries = $ccService->resolveCountriesForDropdown(
            $subscriber,
            array_filter([
                $application->visa_country,
                optional($application->client)->visa_country,
            ])
        );
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $job_roles = $ccService->getClientJobsForSubscriber($subscriber);
        $page = "applications";
        return view('web.add_application', compact('application', 'job_roles', 'user', 'page', 'countries', 'subscriber', 'visaCategories'));
    }

    public function add_new_application(Request $request)
    {
        $ccService = app(CountryCategorySettingsService::class);
        $request->validate(array_merge([
            'job_status' => 'required|string|max:255',
            'job_open_date' => 'required|date|before_or_equal:today',
            'job_completion_date' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => in_array($request->input('job_status'), self::APPLICATION_END_DATE_REQUIRED_STATUSES, true)),
                'after_or_equal:job_open_date',
                'before_or_equal:today',
            ],
        ], $ccService->visaDetailValidationRules($request->input('job_role'))), [
            'job_open_date.before_or_equal' => 'Application Start Date cannot be in the future',
            'job_completion_date.required_if' => 'Application End Date is required',
            'job_completion_date.after_or_equal' => 'Application End Date must be on or after Application Start Date',
            'job_completion_date.before_or_equal' => 'Application End Date cannot be in the future',
        ]);

        $normalizeDate = function ($value) {
            if (!$value) {
                return null;
            }
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            }
        };
        $endDateEditableStatuses = self::APPLICATION_END_DATE_REQUIRED_STATUSES;
        $resolveApplicationEndDate = function ($status, $endDate) use ($endDateEditableStatuses, $normalizeDate) {
            if (!in_array($status, $endDateEditableStatuses, true)) {
                return null;
            }

            return $normalizeDate($endDate);
        };
        $resolveVisaCountry = function ($value) {
            if (!$value) {
                return null;
            }
            if (is_numeric($value)) {
                $country = Countries::find($value);
                return $country ? $country->country_name : null;
            }
            return $value;
        };
        function job_id()
        {
            $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $id = "";
            for ($i = 0; $i < 8; $i++) {
                $id = $id . $ch[rand(0, strlen($ch) - 1)];
            }
            return $id;
        }
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            $application = Applications::find($request->id);
            if ($application) {
                $oldStatus = $application->application_status ?: 'Client Registered';
                $client = Clients::find($request->client_id);
                $subscriber = User::find($client->subscriber_id);
                $ccErrors = $ccService->validateEntrySelection(
                    $subscriber,
                    $resolveVisaCountry($request['visa_country']),
                    $request['job_role']
                );
                if (!empty($ccErrors)) {
                    return back()->withInput()->withErrors($ccErrors);
                }
                $application->application_name = $request['job_role'];
                $application->application_country =  $client->country;
                $application->visa_country =  $resolveVisaCountry($request['visa_country']);
                $application->application_detail = $request['job_detail'];
                $application->application_program = $request['study_program'];
                $application->application_status = $request['job_status'];
                $application->start_date = $normalizeDate($request['job_open_date']);
                $application->end_date = $resolveApplicationEndDate($request['job_status'], $request['job_completion_date']);
                $ccService->applyVisaDetailFields($application, $request['job_role'], $request->all());
                $application->save();
                if ($oldStatus !== $request['job_status']) {
                    ApplicationStatusTrack::create([
                        'application_id' => $application->id,
                        'status' => $request['job_status'],
                        'updated_by' => $user->id,
                        'updated_by_name' => $user->name . ' (' . $user->id . ')',
                        'changed_at' => now(),
                    ]);
                    app(\App\Services\OperationalNotificationService::class)
                        ->notifyApplicationClosure($application, $request['job_status'], $client);
                }
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Application Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "Application of " . $request->job_role . " updated by " . $user->name . " at " . $request->local_time;
                } else {
                    $activity->activity_detail = "Application of " . $request->job_role . " updated by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return redirect()->route('applications')->with('application_updated', "Application updated successfully.");
            } else {
                $client = Clients::find($request->client);
                if ($client) {
                    $subscriber = User::find($client->subscriber_id);
                    $ccErrors = $ccService->validateEntrySelection(
                        $subscriber,
                        $resolveVisaCountry($request['visa_country']),
                        $request['job_role']
                    );
                    if (!empty($ccErrors)) {
                        return back()->withInput()->withErrors($ccErrors);
                    }
                    $application = new Applications();
                    $application->client_id = $client->id;
                    $application->subscriber_id = $subscriber->id;
                    $application->application_id = job_id();
                    $application->application_category = $subscriber->category;
                    $application->application_subcategory = $subscriber->sub_category;
                    $application->application_name = $request['job_role'];
                    $application->application_country =  $client->country;
                     $application->visa_country =  $resolveVisaCountry($request['visa_country']);
                    $application->application_detail = $request['job_detail'];
                    $application->application_program = $request['study_program'];
                    $application->application_status = $request['job_status'];
                    $application->start_date = $normalizeDate($request['job_open_date']);
                    $application->end_date = $resolveApplicationEndDate($request['job_status'], $request['job_completion_date']);
                    $ccService->applyVisaDetailFields($application, $request['job_role'], $request->all());
                    $application->save();
                    ApplicationStatusTrack::create([
                        'application_id' => $application->id,
                        'status' => $request['job_status'],
                        'updated_by' => $user->id,
                        'updated_by_name' => $user->name . ' (' . $user->id . ')',
                        'changed_at' => now(),
                    ]);
                    app(\App\Services\OperationalNotificationService::class)
                        ->notifyApplicationClosure($application, $request['job_status'], $client);
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->activity_name = "New Application Added";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = "New Application of " . $request->job_role . " added by " . $user->name . " at " . $request->local_time;
                    } else {
                        $activity->activity_detail = "New Application of " . $request->job_role . " added by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                    }
                    $activity->activity_icon = "user.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();

                    $mailResult = app(\App\Services\DocumentChecklistMailService::class)
                        ->sendOnApplicationCreated($application, $user, $subscriber);
                    $addedMessage = 'Application added successfully.';
                    if ($mailResult['success']) {
                        $addedMessage .= ' Documents checklist emailed to ' . $mailResult['recipient'] . '.';
                    } elseif (!$mailResult['skipped']) {
                        \Log::warning('Application created but documents checklist email not sent', [
                            'application_id' => $application->id,
                            'message' => $mailResult['message'],
                        ]);
                        $addedMessage .= ' However, the documents checklist email was not sent: ' . $mailResult['message'];
                    }

                    return redirect()->route('applications')->with('application_added', $addedMessage);
                } else {
                    return back();
                }
            }
        } else {
            return redirect()->route('admin');
        }
    }

    public function view_application($id)
    {
        $user = Auth::user();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $application = Applications::find($id);
        if (!$application) {
            return redirect()->route('applications');
        }

        if (!app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return redirect()->route('applications');
        }

        $documentsQuery = Client_Docs::where('application_id', $application->application_id)
            ->whereNotNull('doc_file')
            ->where('doc_file', '!=', '');

        if ($user->user_type !== 'admin') {
            $subscriberId = $user->user_type === 'Subscriber' ? $user->id : $user->added_by;
            $documentsQuery->where('user_id', $subscriberId);
        }

        $documents = $documentsQuery->orderBy('doc_type')->orderByDesc('created_at')->get();
        $ccService = app(\App\Services\CountryCategorySettingsService::class);
        $documentsByFolder = $ccService->groupDocumentsByFolder($documents);
        $documentsByType = $documentsByFolder;
        $documentFolders = $ccService->getDocumentFolders();

        $page = "applications";
        $documentListService = app(\App\Services\ApplicationDocumentListService::class);
        $documentReminderService = app(\App\Services\DocumentReminderService::class);
        $canGenerateDocumentList = $documentListService->hasConfiguredList($user, $application);
        $documentChecklistItems = $documentReminderService->buildDocumentChecklistItems($user, $application);

        return view('web.view_application', compact('application', 'user', 'page', 'documents', 'documentsByType', 'documentsByFolder', 'documentFolders', 'canGenerateDocumentList', 'documentChecklistItems'));
    }

    public function generate_application_document_list($id)
    {
        $user = Auth::user();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $application = Applications::find($id);
        if (!$application) {
            return redirect()->route('applications');
        }

        if (!app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return redirect()->route('applications');
        }

        $documentListService = app(\App\Services\ApplicationDocumentListService::class);

        try {
            return $documentListService->streamPdf($user, $application);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('view_application', $application->id)
                ->with('document_list_error', $e->getMessage());
        }
    }

    public function download_application_document_list($id)
    {
        $user = Auth::user();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $application = Applications::find($id);
        if (!$application) {
            return redirect()->route('applications');
        }

        if (!app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return redirect()->route('applications');
        }

        $documentListService = app(\App\Services\ApplicationDocumentListService::class);

        try {
            return $documentListService->downloadPdf($user, $application);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('view_application', $application->id)
                ->with('document_list_error', $e->getMessage());
        }
    }

    public function send_application_document_list(Request $request, $id)
    {
        $user = Auth::user();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $request->validate([
            'recipient_email' => 'nullable|email|max:255',
            'custom_message' => 'nullable|string|max:2000',
        ]);

        $application = Applications::find($id);
        if (!$application) {
            return redirect()->route('applications');
        }

        if (!app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
            return redirect()->route('applications');
        }

        $documentListService = app(\App\Services\ApplicationDocumentListService::class);
        $client = Clients::find($application->client_id);
        $recipient = trim((string) ($request->input('recipient_email') ?: ($client->email ?? '')));

        if ($recipient === '') {
            return back()->with('document_list_error', 'This client has no email address on record. Add one to the client profile, or enter an address to send to.');
        }

        $mailResult = app(\App\Services\DocumentChecklistMailService::class)->send(
            $application,
            $user,
            null,
            $recipient,
            trim((string) $request->input('custom_message'))
        );

        if (!$mailResult['success']) {
            return back()->with('document_list_error', $mailResult['message']);
        }

        return back()->with('document_list_sent', $mailResult['message']);
    }

    public function send_message(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            $message = new Messages();
            $message->client_id = $request['client_id'];
            $message->message = $request['message'];
            $message->save();
            echo "Message Sent";
        } else {
            return back();
        }
    }

    public function otp($email)
    {
        $email = $email;
        $this->set_timezone();
        return view('web.otp', compact('email'));
    }

    public function moredetails()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->organization != "") {
            return redirect()->route('userprofile');
        }

        $this->set_timezone();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $countries = Countries::all();
        $states = States::all();
        $page = "index";
        return view('web.moredetails', compact('user', 'countries', 'states', 'page', 'tzlist'));
    }

    public function verify_otp(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();
        $this->set_timezone();
        if ($user) {
            // if($request->phone != $request->phone_otp){
            //     return back()->with('phoneerror','Incorrect OTP');
            // }
            if ($request->otp != $user->email_otp) {
                return back()->with('emailerror', 'Incorrect OTP');
            } else {
                $user->email_verified_at = new DateTime('now');
                $user->email_otp = null;
                $user->phone_otp = null;
                $user->save();

                Auth::login($user);

                $deviceId = md5($request->ip() . $request->userAgent());
                UserSession::where('user_id', $user->id)
                    ->where('device_id', $deviceId)
                    ->delete();
                UserSession::create([
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('moredetails');
            }
        } else {
            return back()->with('nouser', 'No user found.');
        }
    }

    public function verify_password_otp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $this->set_timezone();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user found with this email.',
            ]);
        }

        // Sending OTP
        if ($request->action === "send_otp") {
            $eotp = rand(10000, 99999);
            // $eotp = rand(10000, 99999);
            $user->email_otp = $eotp;
            $user->save();

            // Send OTP via email
            $maildata = new \stdClass();
            $maildata->name = $user->name;
            $maildata->email = $user->email;
            $maildata->password = "otp password";
            $maildata->otp = $eotp;

            try {
                Mail::to($user->email)->send(new EmailVerification($maildata));

                if (Mail::failures()) {
                    Log::error('Forgot password OTP email failed: mail transport rejected recipient', [
                        'email' => $user->email,
                        'user_id' => $user->id,
                        'failures' => Mail::failures(),
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to send OTP. Please try again.',
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP sent successfully to your email.',
                ]);
            } catch (\Throwable $e) {
                Log::error('Forgot password OTP email failed', [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send OTP. Please try again.',
                ]);
            }
        }

        // Verifying OTP
        if ($request->action === "verify_otp") {
            if ($user->email_otp != $request->email_otp) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP. Please try again.',
                ]);
            }

            // Clear OTP after verification
            $user->email_otp = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully.',
                'redirect_url' => route('new_password', ['email' => $user->email]),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request.',
        ]);
    }


    public function verify_password_otp_affiliate(Request $request)
    {
        $user = Affiliates::where('email', '=', $request->email)->first();
        $this->set_timezone();
        if ($user) {
            // if($request->phone != $request->phone_otp){
            //     return back()->with('phoneerror','Incorrect OTP');
            // }
            if ($request->email_otp != $user->email_otp) {
                return redirect()->back()->withInput()->with('emailerror', 'Incorrect OTP');
            } else {
                $email = $request->email;
                return redirect()->route('new_password_affiliate', $email);
            }
        } else {
            return back()->with('nouser', 'No user found.');
        }
    }

    public function new_password($email)
    {
        $email = $email;
        $this->set_timezone();
        return view('web.new_password', compact('email'));
    }
    public function new_password_affiliate($email)
    {
        $email = $email;
        $this->set_timezone();
        return view('web.new_password_affiliate', compact('email'));
    }

    public function save_password(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();
        $this->set_timezone();
        if ($user) {
            $this->validate($request, [
                'password' => 'required|string|min:8|confirmed',
            ]);
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $user->email_otp = null;
            $user->phone_otp = null;
            $user->password = Hash::make($request['password']);
            $user->save();
            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Password Recovered";
            if ($user->user_type == "Subscriber") {
                $activity->activity_detail = "Password Recovered by " . $user->name . " at " . $request->local_time;
            } else {
                $activity->activity_detail = "Password Recovered by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
            }
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            return redirect()->route('login')->with('password_changed', 'Password changed successfully.');
        } else {
            return back()->with('nouser', 'No user found.');
        }
    }
    public function save_password_affiliate(Request $request)
    {
        $user = Affiliates::where('email', '=', $request->email)->first();
        $this->set_timezone();
        if ($user) {
            $this->validate($request, [
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user->password = Hash::make($request['password']);
            $user->email_otp = null;
            $user->phone_otp = null;
            $user->save();
            $activity = new Activities();
            $activity->subscriber_id = $user->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Password Recovered";
            $activity->activity_detail = "Password Recovered by " . $user->name . "(" . $user->name . ") at " . $request->local_time;
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            return redirect()->route('login')->with('password_changed', 'Password changed successfully.');
        } else {
            return back()->with('nouser', 'No user found.');
        }
    }

    public function forget_password()
    {
        return view('web.forget_password');
    }

    public function thanks()
    {
        $page = 'index';
        return view('web.thanks', compact('page'));
    }

    public function features()
    {
        $user = Auth::user();
        $this->set_timezone();
        $features = Features::get();
        $page = "features";
        return view('web.features', compact('user', 'features', 'page'));
    }

    public function faqs()
    {
        $user = Auth::user();
        $this->set_timezone();
        
        $faqs = Faq::get();
        $page = "faqs";
            return view('web.faqs', compact('user', 'page', 'faqs'));
    }

    public function membership()
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $myplan = Membership::where('plan_name', '=', $user->membership)->first();
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
                $myplan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            }
            $total_users = User::where('added_by', '=', $subscriber->id)->get();
            $total_clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        } else {
            $myplan = null;
            $subscriber = null;
            $total_users = 0;
            $total_clients = 0;
        }
        $membership = Membership::orderBy('created_at', 'asc')->get();
        $page = "membership";
        return view('web.membership', compact('user', 'membership', 'page', 'myplan', 'subscriber', 'total_users', 'total_clients'));
    }

    public function membershipRenewal()
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $myplan = Membership::where('plan_name', '=', $user->membership)->first();
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
                $myplan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            }
            $total_users = User::where('added_by', '=', $subscriber->id)->get();
            $total_clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        } else {
            $myplan = null;
            $subscriber = null;
            $total_users = 0;
            $total_clients = 0;
        }
        $membership = Membership::orderBy('created_at', 'asc')->get();
        $page = "membership";
        return view('web.membership_renewal', compact('user', 'membership', 'page', 'myplan', 'subscriber', 'total_users', 'total_clients'));
    }

    public function user_membership()
    {

        $this->check_login();
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber" || $user->user_type == "admin") {
                $subscriber = $user;
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
            }
            $myplan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            $effectiveLimits = app(OfferBenefitService::class)->effectiveLimitsForDisplay($subscriber);
            $subscriptionTerm = app(OfferBenefitService::class)->subscriptionTermForDisplay($subscriber, $myplan);

            $membership = Membership::get();
            $page = "user_membership";
            return view('web.user_membership', compact('user', 'membership', 'page', 'myplan', 'subscriber', 'effectiveLimits', 'subscriptionTerm'));
        } else {
            return back();
        }
    }

    public function download_all_data()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->user_type == 'Subscriber' || $user->user_type == 'admin') {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }

        if (!$subscriber) {
            return back()->with('download_error', 'Unable to identify subscriber for export.');
        }

        $visibility = app(ApplicationVisibilityService::class);
        if ($user->user_type === 'User' && !$visibility->hasSubscriberLevelApplicationsAccess($user)) {
            return back()->with('download_error', 'You do not have permission to export all consultancy data.');
        }

        $timestamp = now()->format('Ymd_His');
        $baseDir = storage_path('app/temp_exports/' . $subscriber->id . '_' . $timestamp);
        $tablesDir = $baseDir . '/Tables';
        $documentsDir = $baseDir . '/Documents';

        if (!is_dir($tablesDir) && !mkdir($tablesDir, 0777, true) && !is_dir($tablesDir)) {
            return back()->with('download_error', 'Unable to prepare export directory.');
        }
        if (!is_dir($documentsDir) && !mkdir($documentsDir, 0777, true) && !is_dir($documentsDir)) {
            return back()->with('download_error', 'Unable to prepare documents directory.');
        }

        $clients = Clients::where('subscriber_id', $subscriber->id)->get();
        $applications = Applications::where('subscriber_id', $subscriber->id)->get();
        $users = User::where('added_by', $subscriber->id)->get();
        $invoices = Invoices::where('user_id', $subscriber->id)->get();
        $payments = PaymentARs::where('subscriber_id', $subscriber->id)->get();
        $communications = Internal_communications::where('subscriber_id', $subscriber->id)->get();

        $this->writeExportCsv($tablesDir . '/Clients.csv', $clients->toArray());
        $this->writeExportCsv($tablesDir . '/Applications.csv', $applications->toArray());
        $this->writeExportCsv($tablesDir . '/Users_Staff.csv', $users->toArray());
        $this->writeExportCsv($tablesDir . '/Invoices.csv', $invoices->toArray());
        $this->writeExportCsv($tablesDir . '/Payments.csv', $payments->toArray());
        $this->writeExportCsv($tablesDir . '/Communications.csv', $communications->toArray());

        $clientDocuments = Client_Docs::where('user_id', $subscriber->id)->whereNotNull('doc_file')->get();

        foreach ($clientDocuments as $document) {
            $client = Clients::find($document->client_id);
            $application = Applications::where('application_id', $document->application_id)->first();

            $clientName = $client ? $this->safeArchiveName($client->name) : 'Unknown Client';
            $applicationName = $application ? $this->safeArchiveName($application->application_name ?: $application->application_id) : 'Unknown Application';
            $targetFolder = $documentsDir . '/' . $clientName . ' - ' . $applicationName;

            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }

            $sourcePath = public_path('web_assets/users/client' . $document->client_id . '/docs/' . $document->doc_file);
            if (file_exists($sourcePath)) {
                $destinationName = $document->doc_name ? $this->safeArchiveName($document->doc_name) : pathinfo($document->doc_file, PATHINFO_FILENAME);
                $extension = pathinfo($document->doc_file, PATHINFO_EXTENSION);
                $destinationPath = $targetFolder . '/' . $destinationName . ($extension ? '.' . $extension : '');

                if (file_exists($destinationPath)) {
                    $destinationPath = $targetFolder . '/' . $destinationName . '_' . $document->id . ($extension ? '.' . $extension : '');
                }

                copy($sourcePath, $destinationPath);
            }
        }

        $zipFileName = 'subscriber_data_' . Str::slug($subscriber->name ?: 'subscriber') . '_' . $timestamp . '.zip';
        $zipPath = storage_path('app/temp_exports/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('download_error', 'Unable to create export file.');
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($baseDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        if (is_dir($baseDir)) {
            $directoryIterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($directoryIterator as $item) {
                $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
            }
            rmdir($baseDir);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function wallet()
    {
        $user = $this->check_login();
        if ($user->type_user != "affiliate" && $user->user_type != "admin" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber" || $user->user_type == "admin" || $user->user_type == 'Affiliate') {
                $subscriber = $user;
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
            }
            if ($user->user_type != 'Affiliate') {

                $myplan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            }


            if ($user->user_type == "admin") {
                $referrals = Referrals::walletTableVisible()->orderBy('created_at', 'desc')->get();
                $transactions = Referrals::where('type', '=', 'Wallet Transaction')->orderBy('created_at', 'desc')->get();
            } elseif($user->user_type == 'Affiliate'){
                $referrals = Referrals::whereHas('user')
                ->whereHas('getRefferedByUser')
                ->with(['user'])
                ->where('referral_code', '=', $subscriber->referral)
                ->walletTableVisible()
                ->orderBy('created_at', 'desc')->get();
                $transactions = Referrals::where('userid', '=', $subscriber->id)->where('type', '=', 'Wallet Transaction')->orderBy('created_at', 'desc')->get();
            }else {
                $walletLedger = app(\App\Services\WalletLedgerService::class);
                $referrals = $walletLedger->subscriberWalletEntriesQuery((int) $subscriber->id, $subscriber->referral)->get();
                $transactions = Referrals::where('userid', '=', $subscriber->id)->where('type', '=', 'Wallet Transaction')->orderBy('created_at', 'desc')->get();
            }



            $membership = Membership::get();
            $today = new DateTime("now");
            $membership_expiry = new DateTime($user->membership_expiry_date);
            if ($today > $membership_expiry) {
                $expiry = "Plan Expired";
            } else {
                $expiry = null;
            }
            $page = "wallet";
            if (request()->ajax()) {
                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

                if (request()->tableName == 'wallet'); {
                    $referrals = $referrals->whereBetween('created_at', [$startDate, $endDate]);

                    return DataTables::of($referrals)
                        ->addIndexColumn()
                        ->editColumn('walletId', function ($row) {
                            return $row->id;
                        })
                        ->editColumn('user_name', function ($row) {
                            if (strlen($row->user_name) > 15) {
                                return  substr($row->user_name, 0, 15) . '...';
                            } else {
                                return $row->user_name;
                            }
                        })

                        ->addColumn('finalamount', function ($row) {

                            if (!empty($row->amount_added)) {
                                return $row->amount_added;
                            } elseif (!empty($row->debit_amount)) {
                                return $row->debit_amount;
                            }
                            return 0;
                        })
                        ->addColumn('TransactionType', function ($row) {
                            $wallet_balance = round($row->wallet_balance,2) ?? 0;
                            $previous_balance = round($row->previous_balance,2) ?? 0;
                            $result ='';
                            if($wallet_balance > 0 && $wallet_balance > $previous_balance){
                                $result =  '+'.round(($wallet_balance - $previous_balance),2);
                             }elseif ($previous_balance > 0 && $wallet_balance < $previous_balance){
                             $result = '-'.round(($previous_balance - $wallet_balance),2);
                             }else{
                             $result ='0';
                            }
                            return $result;
                        })
                         ->addColumn('type', function ($row) {
                            return app(\App\Services\WalletLedgerService::class)->walletReferralDescription($row);
                        })
                        ->editColumn('created_at', function ($row) {
                            return date("d-m-Y H:i:s", strtotime($row->created_at));
                        })
                        ->make(true);


                }
                if (request()->tableName == 'transactions') {
                    $transactions = $transactions->whereBetween('created_at', [request()->startdate, request()->enddate]);

                    return DataTables::of($transactions)
                        ->addIndexColumn()
                        ->editColumn('user_name', function ($row) {
                            if (strlen($row->user_name) > 15) {
                                return  substr($row->user_name, 0, 15) . '...';
                            } else {
                                return $row->user_name;
                            }
                        })

                        ->addColumn('TransactionType', function ($row) {
                            if (!empty($row->amount_added)) {
                                return 'Credit';
                            } elseif (!empty($row->debit_amount)) {
                                return 'Debit';
                            }
                            return '';
                        })

                        ->editColumn('created_at', function ($row) {
                            return date("d-m-Y H:i:s", strtotime($row->created_at));
                        })
                        ->make(true);
                }
            }
            if ($user->type_user == 'affiliate') {

                return view('affiliate.wallet', compact('user', 'membership', 'page', 'subscriber', 'referrals', 'expiry', 'transactions'));
            } else {
                return view('web.wallet', compact('user', 'membership', 'page', 'myplan', 'subscriber', 'referrals', 'expiry', 'transactions'));
            }
        } else {
            return back();
        }
    }

    public function add_amount(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $amt = $subscriber->wallet;
                $subscriber->wallet = $amt + abs($request['amount']);
                $subscriber->save();
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->activity_name = "Amount added in Wallet";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = $user->name . " added " . $request->amount . " in his wallet at " . $request->local_time;
                } else {
                    $activity->activity_detail = $user->name . "(" . $subscriber->name . ") added " . $request->amount . " in his wallet at " . $request->local_time;
                }
                $activity->activity_icon = "invoice.jpg";
                $activity->local_time = $request->local_time;
                $activity->save();
                return back()->with('amount_added', 'Amount added successfully.');
            } else {
                return back();
            }
        } else {
            return back();
        }
    }

    public function referrals()
    {
        $user = $this->check_login();
        if ($user->type_user != "affiliate" && membership_access_blocked($user)) {

            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber" || $user->user_type == "Affiliate") {
                $subscriber = $user;
            } else {
                $sid = $user->added_by;
                $subscriber = User::find($sid);
            }
            if ($user->user_type == "admin") {

                $referrals = Referrals::whereNotIn('type', ['one_off', 'double_term', 'cashback'])->where('type','Referral Commission')->orderBy('created_at', 'desc')->get();
            } else {

                $referrals = Referrals::where('userid','!=', $subscriber->id)->where('type','Referral Commission')->where('referral_code', '=', $subscriber->referral)->orderBy('created_at', 'desc')->get();
            }
            $page = "referrals";
            if (request()->ajax()) {
                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

                $referrals = $referrals->whereBetween('created_at', [$startDate, $endDate]);

                return DataTables::of($referrals)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        return date("d-m-Y H:i:s", strtotime($row->created_at));
                    })
                    ->make(true);
            }
            if ($user->type_user == 'affiliate') {
                return view('affiliate.referrals', compact('user', 'page', 'subscriber', 'referrals'));
            } else {
                return view('web.referrals', compact('user', 'page', 'subscriber', 'referrals'));
            }
        } else {
            return back();
        }
    }

    public function upgrade_membership($plan)
    {
        $user = Auth::user();
        $this->set_timezone();
        if (membership_access_blocked($user)) {
            $expired = "expired";
        } else {
            $expired = "";
        }
        $membership = Membership::where('plan_name', '=', $plan)->first();
        if (!$membership) {
            return redirect()->route('membership')->with('membership_expiry', 'Selected plan was not found.');
        }

        $currentPlan = Membership::where('plan_name', '=', $user->membership)->first();
        if ($currentPlan
            && SubscriptionTermPricing::isDowngradePlan($currentPlan, $membership)
            && !SubscriptionTermPricing::isRenewalWindowOpen($user, $currentPlan)) {
            return redirect()->route('membership')->with(
                'membership_expiry',
                'Plan downgrades are only available at renewal, at the end of your subscription term.'
            );
        }

        $page = "membership";
        return view('web.upgrade_membership', compact('user', 'membership', 'page', 'expired'));
        // $user = Auth::user();
        // if($request->id == $user->id){
        //     $user->membership = $request['plan_name'];
        //     $user->save();
        //     echo "success";
        // }
    }

    public function transaction_id()
    {
        $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $id = "";
        for ($i = 0; $i < 10; $i++) {
            $id = $id . $ch[rand(0, strlen($ch) - 1)];
        }
        // $id = "#" . $id;
        $agent = Invoices::where('invoice', '=', $id)->first();
        if ($agent) {
            $this->transaction_id();
        } else {
            return $id;
        }
    }

    public function make_payment(Request $request)
    {
        // print_r($request->all());
        // exit();
        $user = Auth::user();
        $page = "membership";
        if($request->plan_amount != 0){
            if (isset($request->wallet_pay)) {
                if ($request->id == $user->id) {
                    $plan = $request->plan_name;
                    $membership = Membership::where('plan_name', '=', $plan)->first();
                    $currentPlan = Membership::where('plan_name', '=', $user->membership)->first();
                    if ($membership && $currentPlan
                        && SubscriptionTermPricing::isDowngradePlan($currentPlan, $membership)
                        && !SubscriptionTermPricing::isRenewalWindowOpen($user, $currentPlan)) {
                        return redirect()->route('membership')->with(
                            'membership_expiry',
                            'Plan downgrades are only available at renewal, at the end of your subscription term.'
                        );
                    }

                    $duration = $request->plan_duration;
                    $amount = $request->plan_amount;
                    $membership = Membership::where('plan_name', '=', $plan)->first();
                    $duration = SubscriptionTermPricing::normalizeDuration((int) $duration);
                    $plan_amount = SubscriptionTermPricing::calculate((float) $membership->price_per_year, $duration);
                    $previousPlanName = $user->membership;
                    $previousExpiry = !empty($user->membership_expiry_date)
                        ? \Carbon\Carbon::parse($user->membership_expiry_date)
                        : null;
                    $journeyLog = app(\App\Services\UserJourneyLogService::class);
                    $purchaseCategory = $journeyLog->classifySubscriptionPurchase(
                        false,
                        $previousExpiry,
                        $previousPlanName,
                        $membership->plan_name
                    );
                    $wallet_amount = SubscriptionTermPricing::walletCreditForSubscriptionPayment($user, $purchaseCategory);
                    $discount = min((float) $wallet_amount, (float) $plan_amount);
                    $previousWalletBalance = (float) $user->wallet;
                    $walletDebitAmount = (float) $plan_amount;
                    $new_wallet = max(0, (float) $user->wallet - $plan_amount);
                    $user->membership = $membership->plan_name;
                    $user->membership_type = "Subscription";
                    SubscriptionTermPricing::applyMembershipDates(
                        $user,
                        (int) $duration,
                        $purchaseCategory,
                        $previousExpiry
                    );
                    $user->wallet = $new_wallet;
                    $user->save();
                    $my_users = User::where('added_by', '=', $user->id)->get();
                    foreach ($my_users as $myuser) {
                        $myuser->membership = $user->membership;
                        $myuser->membership_type = $user->membership_type;
                        $myuser->membership_start_date = $user->membership_start_date;
                        $myuser->membership_expiry_date = $user->membership_expiry_date;
                        $myuser->wallet = 0;
                        $myuser->save();
                    }
                    $activity = new Activities();
                    $activity->subscriber_id = $user->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->activity_name = "Subscription Updated";
                    $activity->activity_detail = $user->name . " updates account subscription at " . $request['local_time'];
                    $activity->activity_icon = "mmbrcp.png";
                    $activity->local_time = $request['local_time'];
                    $activity->save();
                    $journeyLog->logSubscriptionPurchase(
                        $user,
                        $purchaseCategory,
                        $membership->plan_name,
                        $duration,
                        $user->id,
                        ['previous_plan' => $previousPlanName]
                    );
                    $service_fee = $plan_amount;
                    // $discount = 0;
                    // $tax = ($service_fee + $discount) * (18/100);
                    $tax = 0;
                    $company = User::where('user_type', '=', 'admin')->first();
                    $invoice = new Invoices();
                    $invoice->user_id = $user->id;
                    $invoice->invoice = $this->transaction_id();
                    $invoice->company_name = $company->organization;
                    $invoice->city = $company->city;
                    $invoice->state = $company->state;
                    $invoice->country = $company->country;
                    $invoice->pincode = $company->pincode;
                    $invoice->phone = $company->phone;
                    $invoice->address = $company->address_line;
                    $invoice->logo = $company->organization_logo;
                    $invoice->to_name = $user->name;
                    $invoice->to_company = $user->organization;
                    $invoice->to_city = $user->city;
                    $invoice->to_state = $user->state;
                    $invoice->to_country = $user->country;
                    $invoice->to_pincode = $user->pincode;
                    $invoice->to_phone = $user->phone;
                    $invoice->to_email = $user->email;
                    $invoice->service_fee = $service_fee;
                    $invoice->discount = $discount;
                    $invoice->tax = $tax;
                    $invoice->total = $service_fee - $discount + $tax;
                    $invoice->payment_mode = "Wallet";
                    $invoice->save();
                    $internalInvoice = $this->createAdminApInvoiceAndPayment($user, $company, (float) $invoice->total, "Wallet", SubscriptionTermPricing::subscriptionFeeDetail($membership->plan_name, (int) $duration));
                    $emailSent = $this->sendPlanUpdateMail($user, $membership, $internalInvoice, $company, $previousPlanName, (int) $duration, $purchaseCategory);
                    $this->recordWalletSubscriptionDebit(
                        $user,
                        $membership,
                        $walletDebitAmount,
                        $previousWalletBalance,
                        $previousPlanName
                    );
                    $this->processRenewalCommissionIfEligible(
                        $user,
                        (float) $plan_amount,
                        $previousExpiry,
                        $previousPlanName,
                        $membership->plan_name
                    );
                    $redirect = redirect()->route('user_membership')->with('payment_success', 'Payment completed successfully.');
                    if (!$emailSent) {
                        $redirect->with('email_warning', 'Your payment was successful, but the confirmation email with invoice could not be sent. Please contact support if you need a copy.');
                    }

                    return $redirect;
                } else {
                    return back();
                }
            } else {
                if ($request->id == $user->id) {
                    $plan = $request->plan_name;
                    $membership = Membership::where('plan_name', '=', $plan)->first();
                    $currentPlan = Membership::where('plan_name', '=', $user->membership)->first();
                    if ($membership && $currentPlan
                        && SubscriptionTermPricing::isDowngradePlan($currentPlan, $membership)
                        && !SubscriptionTermPricing::isRenewalWindowOpen($user, $currentPlan)) {
                        return redirect()->route('membership')->with(
                            'membership_expiry',
                            'Plan downgrades are only available at renewal, at the end of your subscription term.'
                        );
                    }

                    $data = array();
                    $duration = $request->plan_duration;
                    $data['id'] = $request->id;
                    $data['plan_name'] = $plan;
                    $data['plan_duration'] = $duration;
                    $membership = Membership::where('plan_name', '=', $plan)->first();
                    $duration = SubscriptionTermPricing::normalizeDuration((int) $duration);
                    $plan_amount = SubscriptionTermPricing::calculate((float) $membership->price_per_year, $duration);
                    $previousPlanName = $user->membership;
                    $previousExpiry = !empty($user->membership_expiry_date)
                        ? \Carbon\Carbon::parse($user->membership_expiry_date)
                        : null;
                    $purchaseCategory = app(\App\Services\UserJourneyLogService::class)->classifySubscriptionPurchase(
                        false,
                        $previousExpiry,
                        $previousPlanName,
                        $membership->plan_name
                    );
                    $wallet_amount = SubscriptionTermPricing::walletCreditForSubscriptionPayment($user, $purchaseCategory);
                    $plan_price = $plan_amount - $wallet_amount;
                    $data['wallet_amount'] = $wallet_amount;
                    $data['plan_amount'] = $plan_amount;
                    $data['plan_price'] = $plan_price;
                    $data['local_time'] = $request->local_time;
                    Session::put('pay_data', $data);
                    // foreach($data as $key => $value){
                    //     echo $key . " = " . $value . "<br>";
                    // }
                    return redirect('pay_securely');

                    // if(isset($request->referral_code)){
                    //     $referral = User::where('id','!=',$user->id)->where('referral','=',$request->referral_code)->first();
                    //     if($referral == null){
                    //         return back()->with('error','Invalid referral code.');
                    //     }
                    //     else{
                    //         $use_referral = Used_referrals::where('subscriber_id','=',$user->id)->where('referral_code','=',$request->referral_code)->first();
                    //         if($use_referral != null){
                    //             return back()->with('used','This referral code has already been used.');
                    //         }
                    //     }
                    // }
                } else {
                    return back();
                }
            }
        } else {
            if ($request->id == $user->id) {
                $plan = $request->plan_name;
                $duration = $request->plan_duration;
                $amount = $request->plan_amount;
                $membership = Membership::where('plan_name', '=', $plan)->first();
                $duration = SubscriptionTermPricing::normalizeDuration((int) $duration);
                $plan_amount = 0;
                $previousPlanName = $user->membership;
                $previousExpiry = !empty($user->membership_expiry_date)
                    ? \Carbon\Carbon::parse($user->membership_expiry_date)
                    : null;
                $journeyLog = app(\App\Services\UserJourneyLogService::class);
                $purchaseCategory = $journeyLog->classifySubscriptionPurchase(
                    false,
                    $previousExpiry,
                    $previousPlanName,
                    $membership->plan_name
                );
                $wallet_amount = SubscriptionTermPricing::walletCreditForSubscriptionPayment($user, $purchaseCategory);
                $discount = min((float) $wallet_amount, (float) $plan_amount);
                $previousWalletBalance = (float) $user->wallet;
                $new_wallet = max(0, (float) $user->wallet - $plan_amount);
                $user->membership = $membership->plan_name;
                $user->membership_type = "Subscription";
                SubscriptionTermPricing::applyMembershipDates(
                    $user,
                    (int) $duration,
                    $purchaseCategory,
                    $previousExpiry
                );
                $user->wallet = $new_wallet;
                $user->save();
                $my_users = User::where('added_by', '=', $user->id)->get();
                foreach ($my_users as $myuser) {
                    $myuser->membership = $user->membership;
                    $myuser->membership_type = $user->membership_type;
                    $myuser->membership_start_date = $user->membership_start_date;
                    $myuser->membership_expiry_date = $user->membership_expiry_date;
                    $myuser->wallet = 0;
                    $myuser->save();
                }
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Subscription Updated";
                $activity->activity_detail = $user->name . " updates account subscription at " . $request['local_time'];
                $activity->activity_icon = "mmbrcp.png";
                $activity->local_time = $request['local_time'];
                $activity->save();
                $journeyLog->logSubscriptionPurchase(
                    $user,
                    $purchaseCategory,
                    $membership->plan_name,
                    $duration,
                    $user->id,
                    ['previous_plan' => $previousPlanName]
                );
                $service_fee = $plan_amount;
                // $discount = 0;
                // $tax = ($service_fee + $discount) * (18/100);
                $tax = 0;
                $company = User::where('user_type', '=', 'admin')->first();
                $invoice = new Invoices();
                $invoice->user_id = $user->id;
                $invoice->invoice = $this->transaction_id();
                $invoice->company_name = $company->organization;
                $invoice->city = $company->city;
                $invoice->state = $company->state;
                $invoice->country = $company->country;
                $invoice->pincode = $company->pincode;
                $invoice->phone = $company->phone;
                $invoice->address = $company->address_line;
                $invoice->logo = $company->organization_logo;
                $invoice->to_name = $user->name;
                $invoice->to_company = $user->organization;
                $invoice->to_city = $user->city;
                $invoice->to_state = $user->state;
                $invoice->to_country = $user->country;
                $invoice->to_pincode = $user->pincode;
                $invoice->to_phone = $user->phone;
                $invoice->to_email = $user->email;
                $invoice->service_fee = $service_fee;
                $invoice->discount = 0;
                $invoice->tax = $tax;
                $invoice->total = $service_fee - 0 + $tax;
                $invoice->payment_mode = "Wallet";
                $invoice->save();
                $internalInvoice = $this->createAdminApInvoiceAndPayment($user, $company, (float) $invoice->total, "Wallet", SubscriptionTermPricing::subscriptionFeeDetail($membership->plan_name, (int) $duration));
                $emailSent = $this->sendPlanUpdateMail($user, $membership, $internalInvoice, $company, $previousPlanName, (int) $duration, $purchaseCategory);
                if ($plan_amount > 0) {
                    $this->recordWalletSubscriptionDebit(
                        $user,
                        $membership,
                        (float) $plan_amount,
                        $previousWalletBalance,
                        $previousPlanName
                    );
                }
                $redirect = redirect()->route('user_membership')->with('payment_success', 'Payment completed successfully.');
                if (!$emailSent) {
                    $redirect->with('email_warning', 'Your payment was successful, but the confirmation email with invoice could not be sent. Please contact support if you need a copy.');
                }

                return $redirect;
            } else {
                return back();
            }
        }
    }

    public function pay_securely()
    {
        $user = Auth::user();
        if ($user) {
            $page = "membership";
            $data = session('pay_data');
            $amount = $data['plan_price'];
            return view('web.make_payment', compact('user', 'data', 'page', 'amount'));
        } else {
            return back();
        }
    }

    public function upgrade_plan(Request $request)
    {
        function invoice_id()
        {
            $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $id = "";
            for ($i = 0; $i < 10; $i++) {
                $id = $id . $ch[rand(0, strlen($ch) - 1)];
            }
            if (Invoices::where('invoice', '=', $id)->first()) {
                return invoice_id();
            }
            return $id;
        }
        $user = Auth::user();
        $paymentMode = isset($request->wallet_pay) ? 'Wallet' : 'Card';
        if ($request->id == $user->id) {
            $targetPlan = Membership::where('plan_name', '=', $request['plan_name'])->first();
            $currentPlan = Membership::where('plan_name', '=', $user->membership)->first();
            if ($targetPlan && $currentPlan
                && SubscriptionTermPricing::isDowngradePlan($currentPlan, $targetPlan)
                && !SubscriptionTermPricing::isRenewalWindowOpen($user, $currentPlan)) {
                return redirect()->route('membership')->with(
                    'membership_expiry',
                    'Plan downgrades are only available at renewal, at the end of your subscription term.'
                );
            }

            $previousWalletBalance = (float) $user->wallet;
            $walletDebitAmount = 0.0;
            if (isset($request->wallet_pay)) {
                $walletDebitAmount = (float) $request->plan_price;
                $user->wallet = $previousWalletBalance - $walletDebitAmount;
            }
            if (isset($request->referral_code)) {
                $referral = User::where('id', '!=', $user->id)->where('referral', '=', $request->referral_code)->first();
                if ($referral == null) {
                    return back()->with('error', 'Invalid referral code.');
                } else {
                    $use_referral = Used_referrals::where('subscriber_id', '=', $user->id)->where('referral_code', '=', $request->referral_code)->first();
                    if ($use_referral != null) {
                        return back()->with('used', 'This referral code has already been used.');
                    }
                }
            }
            $duration = $request['plan_duration'];
            $previousPlanName = $user->membership;
            $previousExpiry = !empty($user->membership_expiry_date)
                ? \Carbon\Carbon::parse($user->membership_expiry_date)
                : null;
            $durationYears = SubscriptionTermPricing::normalizeDuration((int) $duration);
            $journeyLog = app(\App\Services\UserJourneyLogService::class);
            $purchaseCategory = $journeyLog->classifySubscriptionPurchase(
                false,
                $previousExpiry,
                $previousPlanName,
                $request['plan_name']
            );
            $user->membership = $request['plan_name'];
            $user->membership_type = "Subscription";
            SubscriptionTermPricing::applyMembershipDates(
                $user,
                $durationYears,
                $purchaseCategory,
                $previousExpiry
            );
            $user->save();
            $my_users = User::where('added_by', '=', $user->id)->get();
            foreach ($my_users as $myuser) {
                $myuser->membership = $user->membership;
                $myuser->membership_type = $user->membership_type;
                $myuser->membership_start_date = $user->membership_start_date;
                $myuser->membership_expiry_date = $user->membership_expiry_date;
                $myuser->save();
            }
            // if(isset($referral)){
            //     $amt = $referral->wallet;
            //     $referral->wallet = $amt + ($request->plan_price * 0.2);
            //     $referral->save();
            //     $save_referral = new Referrals();
            //     $save_referral->referral_code = $request->referral_code;
            //     $save_referral->userid = $user->id;
            //     $save_referral->user_name = $user->name;
            //     $save_referral->total_amount = $request->plan_price;
            //     $save_referral->amount_added = $request->plan_price * 0.2;
            //     $save_referral->previous_balance = $amt;
            //     $save_referral->wallet_balance = $amt + ($request->plan_price * 0.2);
            //     $save_referral->save();
            // }
            $activity = new Activities();
            $activity->subscriber_id = $user->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Price Plan Updated";
            $activity->activity_detail = $user->name . " updates account price plan at " . $request->local_time;
            $activity->activity_icon = "mmbrcp.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            $journeyLog->logSubscriptionPurchase(
                $user,
                $purchaseCategory,
                $request['plan_name'],
                $durationYears,
                $user->id,
                ['previous_plan' => $previousPlanName]
            );
            $plan = Membership::where('plan_name', '=', $request->plan_name)->first();
            $service_fee = $request->plan_price;
            $discount = 0;
            // Packaged subscription prices are fixed — never apply Invoice Settings or GST markup.
            $tax = 0;
            $company = User::where('user_type', '=', 'admin')->first();
            $invoice = new Invoices();
            $invoice->user_id = $user->id;
            $invoice->invoice = invoice_id();
            $invoice->company_name = $company->organization;
            $invoice->city = $company->city;
            $invoice->state = $company->state;
            $invoice->country = $company->country;
            $invoice->pincode = $company->pincode;
            $invoice->phone = $company->phone;
            $invoice->address = $company->address_line;
            $invoice->logo = $company->organization_logo;
            $invoice->to_name = $user->name;
            $invoice->to_company = $user->organization;
            $invoice->to_city = $user->city;
            $invoice->to_state = $user->state;
            $invoice->to_country = $user->country;
            $invoice->to_pincode = $user->pincode;
            $invoice->to_address = $user->address_line;
            $invoice->to_phone = $user->phone;
            $invoice->to_email = $user->email;
            $invoice->service_fee = $service_fee;
            $invoice->discount = $discount;
            $invoice->tax = $tax;
            $invoice->total = $service_fee - $discount + $tax;
            $invoice->save();
            $internalInvoice = $this->createAdminApInvoiceAndPayment($user, $company, (float) $invoice->total, $paymentMode, SubscriptionTermPricing::subscriptionFeeDetail($plan->plan_name, $durationYears));
            $emailSent = $this->sendPlanUpdateMail($user, $plan, $internalInvoice, $company, $previousPlanName, $durationYears, $purchaseCategory);
            if ($walletDebitAmount > 0) {
                $this->recordWalletSubscriptionDebit(
                    $user,
                    $plan,
                    $walletDebitAmount,
                    $previousWalletBalance,
                    $previousPlanName
                );
            }
            $this->processRenewalCommissionIfEligible(
                $user,
                (float) $request->plan_price,
                $previousExpiry,
                $previousPlanName,
                $request['plan_name']
            );
            $activity = new Activities();
            $activity->subscriber_id = $user->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Payment Generated";
            $activity->activity_detail = "Payment generated for price plan update for user " . $user->name . " at " . $request->local_time;
            $activity->activity_icon = "invoice.jpg";
            $activity->local_time = $request->local_time;
            $activity->save();
            $redirect = redirect()->route('user_membership');
            if (!$emailSent) {
                $redirect->with('email_warning', 'Your subscription was updated, but the confirmation email with invoice could not be sent. Please contact support if you need a copy.');
            }

            return $redirect;
        }
    }

    public function downgrade_plan(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($request->id != $user->id) {
            return response('forbidden', 403);
        }

        $currentPlan = Membership::where('plan_name', '=', $user->membership)->first();
        $targetPlan = Membership::where('plan_name', '=', $request->plan_name)->first();

        if (!$currentPlan || !$targetPlan || !SubscriptionTermPricing::isDowngradePlan($currentPlan, $targetPlan)) {
            return response('invalid_plan', 422);
        }

        if (!SubscriptionTermPricing::isRenewalWindowOpen($user, $currentPlan)) {
            return response('downgrade_not_allowed', 403);
        }

        if ($request->id == $user->id) {
            $user->membership = $request['plan_name'];
            $user->save();
            $activity = new Activities();
            $activity->subscriber_id = $user->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Price Plan Updated";
            $activity->activity_detail = $user->name . " updates account price plan at " . $request->local_time;
            $activity->activity_icon = "mmbrcp.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            echo "success";
        }
    }



    public function view_payment($id)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $roles = UserRoles::where('user_id', '=', $user->id)->first();
        $page = "payments";
        $invoice = Invoices::find($id);
        return view('web.view_payment', compact('user', 'roles', 'page', 'invoice'));
    }

    public function print_payment($id)
    {
        $user = $this->check_login();
        if ($user->user_type != "admin") {
            if (membership_access_blocked($user)) {
                return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
            }
        }
        $page = "payments";
        $invoice = Invoices::find($id);
        return view('web.print_payment', compact('user', 'page', 'invoice'));
    }

    public function delete_payment($id = null, $localtime = null)
    {
        if (!empty($id)) { //edit the page.
            $user = Auth::user();
            $this->set_timezone();
            if ($user) {
                $invoice = Invoices::find($id);
                $invoice->delete();
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Payment Deleted";
                $activity->activity_detail = $user->name . " deleted an Invoice at " . $localtime;
                $activity->activity_icon = "invoice.jpg";
                $activity->local_time = $localtime;
                $activity->save();
                return back()->with('deleted', 'Job deleted successfully.');
            } else {
                return redirect()->route('login');
            }
        } else { //view the page.
            return back();
        }
    }

    public function invoices()
    {
        $user = $this->check_login();
        if ($user->user_type != "admin" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        } else {
            $subscriber = User::find($user->added_by);
            $clients = Clients::where('user_id', '=', $user->id)->get();
        }
        if ($user->user_type == "admin") {

            $invoices = Internal_Invoices::orderBy('created_at', 'desc')->where('type', 'ar')->get();
        } else {
            $invoices = Internal_Invoices::where('subscriber_id', '=', $subscriber->id)->where('type', 'ar')->orderBy('created_at', 'desc')->get();
        }
        $roles = UserRoles::where('user_id', '=', $user->id)->first();
        $page = "invoices";

        if (request()->ajax()) {
            $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
            $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

            $invoice_roles = null;
            if ($user->user_type != "admin") {

                $invoice_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Invoices')->first();
            }
            $invoices = $invoices->whereBetween('created_at', [$startDate, $endDate]);

            return DataTables::of($invoices)
                ->addIndexColumn()
                ->editColumn('to_name', function ($row) {
                    return trim($row->to_name . (!empty($row->vendor_id) ? ' (' . $row->vendor_id . ')' : ''));
                })
                ->editColumn('to_email', function ($row) {

                    return $row->to_email;
                })
                ->editColumn('discount', function ($row) {
                    return $row->discount . " %";
                })
                ->editColumn('tax', function ($row) {
                    return $row->tax . " %";
                })
                ->addColumn('sub_type', function ($row) use ($user) {

                    return $row->status;
                })
                ->editColumn('due_date', function ($row) {
                    return date("d-m-Y", strtotime($row->due_date));
                })
                ->addColumn('action', function ($row) use ($invoice_roles, $user) {
                    $html = '<a style="background:none; border:none;"';

                    if ($user->user_type == "admin" || $invoice_roles->read_only == 1 || $invoice_roles->read_write_only == 1) {
                        $html .= ' href="' . route('view_invoice', $row->id) . '"';
                    } else {
                        $html .= ' href="#"';
                    }

                    $html .= ' class="m-0 p-0"><i class="fa-solid fa-eye p-1 text-info" style="font-size:14px;"></i></a>';

                    if ($user->user_type == "admin" || $invoice_roles->write_only == 1 || $invoice_roles->read_write_only == 1) {
                        $html .= ' <a style="background:none; border:none;" href="' . route('edit_invoice', $row->id) . '" class="m-0 p-0" title="Edit Invoice"><i class="fa-solid fa-pen-to-square p-1 text-primary" style="font-size:14px;"></i></a>';
                    }

                    return $html;
                })
                ->make(true);
        }
        $invoiceStatusFilters = TableFilterCountService::countBy(
            $invoices,
            fn ($invoice) => TableFilterCountService::invoiceStatusLabel($invoice->status)
        );
        return view('web.invoices', compact('user', 'page', 'invoices', 'roles', 'clients', 'invoiceStatusFilters'));
    }

    public function invoice_payment_made(){
        $user = $this->check_login();
        if ($user->user_type != "admin" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
        } else {
            $subscriber = User::find($user->added_by);
            $clients = Clients::where('user_id', '=', $user->id)->get();
        }
        if ($user->user_type == "admin") {

            $invoices = Internal_Invoices::orderBy('created_at', 'desc')->where('type', 'ap')->get();
        } else {
            $invoices = Internal_Invoices::where('subscriber_id', '=', $subscriber->id)->where('type', 'ap')->orderBy('created_at', 'desc')->get();
        }
        $roles = UserRoles::where('user_id', '=', $user->id)->first();
        $page = "invoices";

        if (request()->ajax()) {
            $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
            $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

            $invoice_roles = null;
            if ($user->user_type != "admin") {

                $invoice_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Invoices')->first();
            }
            $invoices = $invoices->whereBetween('created_at', [$startDate, $endDate]);

            return DataTables::of($invoices)
                ->addIndexColumn()
                ->editColumn('to_name', function ($row) {
                    return $row->apVendorDisplay();
                })
                ->editColumn('to_email', function ($row) {

                    return $row->to_email;
                })
                ->editColumn('discount', function ($row) {
                    return $row->discount . " %";
                })
                ->editColumn('tax', function ($row) {
                    return $row->tax . " %";
                })
                ->addColumn('sub_type', function ($row) use ($user) {

                    return $row->status;
                })
                ->editColumn('due_date', function ($row) {
                    return date("d-m-Y", strtotime($row->due_date));
                })
                ->addColumn('action', function ($row) use ($invoice_roles, $user) {
                    $html = '<a style="background:none; border:none;"';

                    if ($user->user_type == "admin" || $invoice_roles->read_only == 1 || $invoice_roles->read_write_only == 1) {
                        $html .= ' href="' . route('view_invoice', $row->id) . '"';
                    } else {
                        $html .= ' href="#"';
                    }

                    $html .= ' class="m-0 p-0"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>';

                    if ($user->user_type == "admin" || $invoice_roles->write_only == 1 || $invoice_roles->read_write_only == 1) {
                        $html .= ' <a style="background:none; border:none;" href="' . route('edit_invoice_ap', $row->id) . '" class="m-0 p-0" title="Edit Invoice"><i class="fa-solid fa-pen-to-square p-1 text-primary" style="font-size:14px;"></i></a>';
                    }

                    return $html;
                })
                ->make(true);
        }
        $invoiceStatusFilters = TableFilterCountService::countBy(
            $invoices,
            fn ($invoice) => TableFilterCountService::invoiceStatusLabel($invoice->status)
        );
        return view('web.invoice_payment_made', compact('user', 'page', 'invoices', 'roles', 'clients', 'invoiceStatusFilters'));
    }

    public function new_invoice()
    {
        $user = $this->check_login();
        $this->set_timezone();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
        $clients = Clients::where('subscriber_id', '=', $subscriber->id)->orderBy('name')->get();
        if (count($clients) < 1) {
            return back()->with('noclient', true);
        }
        $ccService = app(CountryCategorySettingsService::class);
        $countries = $ccService->resolveCountriesForDropdown($subscriber);
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $page = "invoices";
        $invSetting = Invoice_settings::forUser((int) $subscriber->id);
        $taxMeta = $this->invoiceSettingsTaxMeta($invSetting);

        return view('web.add_invoice', array_merge(compact('clients', 'user', 'page', 'countries', 'visaCategories'), $taxMeta));
    }

    public function new_invoice_ap()
    {
        $user = $this->check_login();
        $this->set_timezone();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }
        $ccService = app(CountryCategorySettingsService::class);
        $countries = $ccService->resolveCountriesForDropdown($subscriber);
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $page = "invoices";
        $invSetting = Invoice_settings::forUser((int) $subscriber->id);
        $taxMeta = $this->invoiceSettingsTaxMeta($invSetting);

        return view('web.add_invoice_ap', array_merge(compact('user', 'page', 'countries', 'visaCategories'), $taxMeta));
    }

    private function normalizeInvoiceDueDate($input): ?string
    {
        $value = trim((string) $input);

        if ($value === '') {
            return null;
        }

        $formats = ['d-m-y', 'd-m-Y', 'Y-m-d'];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {
                // Try the next format
            }
        }

        return $value;
    }

    private function isExportServiceTaxExemptRequest(Request $request): bool
    {
        return (int) $request->input('export_service_tax_exempt', 0) === 1;
    }

    private function resolveArInvoiceTaxPercent(Request $request, ?Invoice_settings $invSetting, bool $exportExempt): float
    {
        if ($exportExempt) {
            return 0.0;
        }

        if ($request->filled('tax')) {
            return max(0, min(100, (float) $request->tax));
        }

        return max(0, min(100, (float) ($invSetting->tax ?? 0)));
    }

    private function invoiceSettingsTaxMeta(?Invoice_settings $invSetting): array
    {
        return [
            'defaultTaxPercent' => max(0, min(100, (float) ($invSetting->tax ?? 0))),
            'taxLabel' => Invoice_settings::resolveTaxLabel($invSetting->tax_label ?? null),
            'invoiceNote' => trim((string) ($invSetting->invoice_note ?? '')),
        ];
    }

    private function generateApVendorId(int $subscriberId): string
    {
        do {
            $candidate = 'VND-' . $subscriberId . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            $exists = Internal_Invoices::where('vendor_id', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }

    private function invoiceItemService(): InvoiceItemService
    {
        return app(InvoiceItemService::class);
    }

    private function persistInvoiceItems(
        Internal_Invoices $invoice,
        Request $request,
        ?int $clientId = null,
        ?int $ignoreInvoiceId = null,
        bool $allowDuplicate = false
    ): array {
        $itemService = $this->invoiceItemService();
        $items = $itemService->normalizeRequestItems($request);
        $itemService->assertHasItems($items);

        if ($clientId && $invoice->type === 'ar' && !$allowDuplicate) {
            $itemService->assertClientApplicationsAvailable(
                (int) $invoice->subscriber_id,
                $clientId,
                $items,
                $ignoreInvoiceId ?? (int) $invoice->id
            );
        }

        $itemService->applyAggregatesToInvoice($invoice, $items);
        $invoice->save();
        $itemService->syncInvoiceItems($invoice, $items);

        return $items;
    }

    private function appendInvoiceItemsToMailData(\stdClass $maildata, Internal_Invoices $invoice): void
    {
        $maildata->items = $this->invoiceItemService()->itemsForMail($invoice);
    }

    private function appendInvoiceBillToMailData(\stdClass $maildata, Internal_Invoices $invoice): void
    {
        $maildata->to_address = $invoice->to_address;
        $maildata->to_city = $invoice->to_city;
        $maildata->to_state = $invoice->to_state;
        $maildata->to_country = $invoice->to_country;
        $maildata->to_pincode = $invoice->to_pincode;
    }

    private function appendInvoicePaymentMailData(\stdClass $maildata, Internal_Invoices $invoice, int $subscriberId): void
    {
        $maildata->payment_link = $invoice->payment_link;
        $maildata->payment_qr_url = $this->invoicePaymentQrUrl($subscriberId, $invoice->payment_qr_code);
        $maildata->payment_qr_path = null;

        if (!empty($invoice->payment_qr_code)) {
            $qrPath = public_path('web_assets/users/user' . $subscriberId . '/' . $invoice->payment_qr_code);
            if (file_exists($qrPath)) {
                $maildata->payment_qr_path = $qrPath;
            }
        }
    }

    public function check_duplicate_invoice(Request $request)
    {
        $request->validate([
            'type' => 'required|in:client,associate',
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'required|string|max:100',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['duplicate' => false], 401);
        }

        $subscriber = $user->user_type === 'Subscriber'
            ? $user
            : User::find($user->added_by);

        if (!$subscriber) {
            return response()->json(['duplicate' => false], 403);
        }

        $itemService = $this->invoiceItemService();
        $duplicate = false;

        if ($request->type === 'associate') {
            $duplicate = $itemService->hasActiveAssociateClientApplicationInvoice(
                (int) $subscriber->id,
                (int) $request->client_id,
                (int) $request->application_id
            );
        } else {
            $duplicate = $itemService->hasActiveClientApplicationInvoice(
                (int) $subscriber->id,
                (int) $request->client_id,
                (string) $request->application_id
            );
        }

        return response()->json(['duplicate' => $duplicate]);
    }

    public function create_new_invoice(Request $request)
    {
        $request->validate(array_merge(
            $this->invoiceItemService()->singleItemValidationRules(),
            ['client' => 'required|exists:clients,id']
        ));
        $user = Auth::user();
        $this->set_timezone();
        $subId =  (Auth::user()->user_type == 'Subscriber') ? $user->id :$user->added_by;
        $inv_setting = Invoice_settings::forUser((int) $subId);
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if ($request->client) {
                $client = Clients::find($request->client);
                $invoice = new Internal_Invoices();
                $invoice->invoice_no = $this->generateInternalInvoiceId();
                $invoice->subscriber_id = $subscriber->id;
                $invoice->user_id = $user->id;
                $invoice->name = $subscriber->name;
                $invoice->email = $subscriber->email;
                $invoice->phone = $subscriber->phone;
                $invoice->country = $subscriber->country;
                $invoice->state = $subscriber->state;
                $invoice->city = $subscriber->city;
                $invoice->pincode = $subscriber->pincode;
                $invoice->address = $subscriber->address_line;
                $invoice->logo = $subscriber->organization_logo;
                $invoice->to_name = $client->name;
                $invoice->to_email = $client->email;
                $invoice->to_phone = $client->phone;
                $invoice->to_country = $client->country;
                $invoice->to_state = $client->state;
                $invoice->to_city = $client->city;
                $invoice->to_pincode = $client->pincode;
                $invoice->to_address = $client->address;
                $invoiceAmount = $this->invoiceItemService()->sumAmounts(
                    $this->invoiceItemService()->normalizeRequestItems($request)
                );
                $discountPercent = max(0, min(100, (float) ($inv_setting->discount ?? 0)));
                $taxPercent = max(0, min(100, (float) ($inv_setting->tax ?? 0)));
                $discountRate = $discountPercent / 100;
                $taxRate = $taxPercent / 100;

                $invoice->type = 'ar';
                $invoice->discount = $discountPercent;
                $invoice->export_service_tax_exempt = $taxPercent == 0.0;
                $invoice->tax = $taxPercent;
                $subtotal = $invoiceAmount - ($invoiceAmount * $discountRate);
                $invoice->total = max(0, $subtotal + ($subtotal * $taxRate));
                $invoice->status = $request['status'];
                $invoice->due_date = $this->normalizeInvoiceDueDate($request['due_date']);
                $invoice->token = $this->generateInternalInvoiceToken();
                app(InvoiceSnapshotService::class)->applySettingsSnapshot($invoice, $inv_setting);
                app(InvoiceAuditService::class)->markCreated($invoice, $user);
                $this->persistInvoiceItems(
                    $invoice,
                    $request,
                    (int) $request->client,
                    null,
                    $request->boolean('confirm_duplicate')
                );

                app(InvoiceAuditService::class)->syncLegacyInvoiceIfPaid($invoice, $user);
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Invoice Generated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "Invoice generated by " . $user->name . " at " . $request->local_time;
                } else {
                    $activity->activity_detail = "Invoice generated by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                }
                $activity->activity_icon = "invoice.jpg";
                $activity->local_time = $request->local_time;
                $activity->save();

                $mailResult = app(InvoiceMailService::class)->send($invoice, $subscriber);
                if (!$mailResult['success']) {
                    \Log::warning('Subscriber invoice email not sent', [
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'message' => $mailResult['message'],
                    ]);
                }

                return redirect()->route('invoices')->with(
                    $mailResult['success'] ? 'invoice_generated' : 'invoice_email_failed',
                    $mailResult['success']
                        ? 'Invoice created and emailed to ' . $mailResult['recipient'] . '.'
                        : 'Invoice created, but email was not sent: ' . $mailResult['message']
                );
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function create_new_invoice_ap(Request $request)
    {
        $request->validate(array_merge(
            $this->invoiceItemService()->validationRules(),
            [
            'invoice_vendor_id' => 'required|string|min:2|max:100',
            'vendor_name' => 'required|string|min:2|max:150',
            'amount' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'tax' => 'required|numeric|min:0|max:100',
            'total_to_pay' => 'required|numeric|min:0',
            'upload_invoice' => 'required|file|mimes:pdf|max:10240',
            ]
        ));
        $user = Auth::user();
        $this->set_timezone();
        $subId =  (Auth::user()->user_type == 'Subscriber') ? $user->id :$user->added_by;
        $inv_setting = Invoice_settings::forUser((int) $subId);
        
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $client = $request->client ? Clients::find($request->client) : null;
            $vendorName = trim((string) $request->vendor_name);
            $vendorInvoiceId = trim((string) $request->invoice_vendor_id);
            $items = $this->invoiceItemService()->normalizeRequestItems($request);
            $this->invoiceItemService()->assertHasItems($items);
            $vendorId = $this->generateApVendorId((int) $subscriber->id);
            $invoice = new Internal_Invoices();
                $invoice->invoice_no = $vendorInvoiceId;
                $invoice->vendor_id = $vendorId;
                $invoice->subscriber_id = $subscriber->id;
                $invoice->user_id = $user->id;
                $invoice->name = $subscriber->name;
                $invoice->email = $subscriber->email;
                $invoice->phone = $subscriber->phone;
                $invoice->country = $subscriber->country;
                $invoice->state = $subscriber->state;
                $invoice->city = $subscriber->city;
                $invoice->pincode = $subscriber->pincode;
                $invoice->address = $subscriber->address_line;
                $invoice->logo = $subscriber->organization_logo;
                $invoice->to_name = $vendorName;
                $invoice->to_email = optional($client)->email;
                $invoice->to_phone = optional($client)->phone;
                $invoice->to_country = optional($client)->country;
                $invoice->to_state = optional($client)->state;
                $invoice->to_city = optional($client)->city;
                $invoice->to_pincode = optional($client)->pincode;
                $invoice->to_address = optional($client)->address;
                $invoiceAmount = $this->invoiceItemService()->sumAmounts($items);
                $discountPercent = max(0, min(100, (float) $request->discount));
                $taxPercent = max(0, min(100, (float) $request->tax));
                $discountRate = $discountPercent / 100;
                $taxRate = $taxPercent / 100;

                $invoice->type = 'ap';
                $invoice->discount = $discountPercent;
                $invoice->tax = $taxPercent;
                $subtotal = $invoiceAmount - ($invoiceAmount * $discountRate);
                $calculatedTotal = max(0, $subtotal + ($subtotal * $taxRate));
                $invoice->total = (float) $request->total_to_pay > 0 ? (float) $request->total_to_pay : $calculatedTotal;
                $invoice->status = $request['status'];
                $invoice->due_date = $this->normalizeInvoiceDueDate($request['due_date']);
                $invoice->token = $this->generateInternalInvoiceToken();
                if ($request->hasFile('upload_invoice')) {
                    $pdfFile = $request->file('upload_invoice');
                    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $pdfFile->getClientOriginalName());
                    $destinationPath = 'web_assets/users/user' . $subscriber->id . '/invoice_uploads';
                    $pdfFile->move($destinationPath, $fileName);
                    $invoice->uploaded_invoice = 'user' . $subscriber->id . '/invoice_uploads/' . $fileName;
                }
                app(InvoiceSnapshotService::class)->applySettingsSnapshot($invoice, $inv_setting);
                app(InvoiceAuditService::class)->markCreated($invoice, $user);
                $this->persistInvoiceItems($invoice, $request);

                app(InvoiceAuditService::class)->syncLegacyInvoiceIfPaid($invoice, $user);
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Invoice Generated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "Invoice generated by " . $user->name . " at " . $request->local_time;
                } else {
                    $activity->activity_detail = "Invoice generated by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                }
                $activity->activity_icon = "invoice.jpg";
                $activity->local_time = $request->local_time;
                $activity->save();

                $mailResult = app(InvoiceMailService::class)->send($invoice, $subscriber);
                if (!$mailResult['success']) {
                    \Log::warning('AP invoice email not sent', [
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'message' => $mailResult['message'],
                    ]);
                }

            return redirect()->route('invoice_payment_made')->with(
                $mailResult['success'] ? 'invoice_generated' : 'invoice_email_failed',
                $mailResult['success']
                    ? 'Invoice created and emailed to ' . $mailResult['recipient'] . '.'
                    : 'Invoice created, but email was not sent: ' . $mailResult['message']
            );
        } else {
            return redirect()->route('login');
        }
    }

    public function view_invoice($id)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $roles = UserRoles::where('user_id', '=', $user->id)->first();
        $page = "invoices";
        $invoice = Internal_Invoices::find($id);
        $u = User::where('email', '=', $invoice->email)->first();
        $invoiceSetting = Invoice_settings::forUser((int) $u->id);
        return view('web.view_invoice', compact('user', 'u', 'page', 'invoice', 'roles', 'invoiceSetting'));
    }

    public function resendInvoiceEmail(Request $request, $id)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $invoice = Internal_Invoices::with('items')->findOrFail($id);
        $subscriber = User::find($invoice->subscriber_id ?: $invoice->user_id);
        $result = app(InvoiceMailService::class)->send($invoice, $subscriber, $request->input('to'));

        return back()->with(
            $result['success'] ? 'invoice_email_sent' : 'invoice_email_failed',
            $result['message']
        );
    }

    public function invoice_preview($id, $token)
    {

        $invoice = Internal_Invoices::where('id', '=', $id)->where('token', '=', $token)->first();
        if ($invoice) {
            $u = User::where('email', '=', $invoice->email)->first();
            $invoiceSetting = Invoice_settings::forUser((int) $u->id);
            return view('web.invoice_preview', compact('u', 'invoice', 'invoiceSetting'));
        } else {
            echo "NO INVOICE FOUND.";
            exit();
        }
    }

    public function print_invoice($id)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        $page = "invoices";
        $invoice = Internal_Invoices::find($id);
        $u = User::where('email', '=', $invoice->email)->first();
        $invoiceSetting = Invoice_settings::forUser((int) $u->id);
        return view('web.print_invoice', compact('user', 'u', 'page', 'invoice', 'invoiceSetting'));
    }

    private function invoicePaymentQrUrl(int $userId, ?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $path = public_path('web_assets/users/user' . $userId . '/' . $filename);
        if (!file_exists($path)) {
            return null;
        }

        return asset('web_assets/users/user' . $userId . '/' . $filename);
    }

    public function delete_invoice($id = null, $localtime = null)
    {
        if (!empty($id)) { //edit the page.
            $user = Auth::user();
            $this->set_timezone();
            if ($user) {
                $invoice = Invoices::find($id);
                $invoice->delete();
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Invoice Deleted";
                $activity->activity_detail = $user->name . " deleted an Invoice at " . $localtime;
                $activity->activity_icon = "invoice.jpg";
                $activity->local_time = $localtime;
                $activity->save();
                return back()->with('deleted', 'Job deleted successfully.');
            } else {
                return redirect()->route('login');
            }
        } else { //view the page.
            return back();
        }
    }

    public function invoice_status(Request $request)
    {
        if (!empty($request->id)) { //edit the page.
            $user = Auth::user();
            if ($user) {
                $invoice = Internal_Invoices::find($request->id);
                $invoice->status = $request->status;
                $auditService = app(InvoiceAuditService::class);
                $auditService->ensureCreatedAudit($invoice);
                $auditService->markUpdated($invoice, $user);
                $invoice->save();
                $auditService->syncLegacyInvoiceIfPaid($invoice, $user);
                $subscriberId = $user->user_type === 'Subscriber' ? $user->id : (int) $user->added_by;
                $auditService->logActivity(
                    $user,
                    $subscriberId,
                    'Invoice Updated',
                    $user->name . ' updated invoice status to ' . $request->status . ' at ' . $request->localtime,
                    $request->localtime
                );
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'no user']);
            }
        } else { //view the page.
            return response()->json(['status' => 'no invoice']);
        }
    }

    private function getInvoiceSubscriber(User $user): User
    {
        return $user->user_type === 'Subscriber' ? $user : User::find($user->added_by);
    }

    private function userCanEditInvoices(User $user): bool
    {
        if ($user->user_type === 'Subscriber') {
            return true;
        }

        $roles = UserRoles::where('user_id', $user->id)->where('module', 'Invoices')->first();

        return $roles && ($roles->write_only == 1 || $roles->read_write_only == 1);
    }

    private function authorizeInvoiceForUser(User $user, Internal_Invoices $invoice): void
    {
        $subscriber = $this->getInvoiceSubscriber($user);
        if ((int) $invoice->subscriber_id !== (int) $subscriber->id) {
            abort(403, 'Unauthorized invoice access.');
        }
    }

    private function resolveInvoiceClientId(Internal_Invoices $invoice, User $subscriber): ?int
    {
        $clientQuery = Clients::where('subscriber_id', $subscriber->id);

        if (!empty($invoice->to_email)) {
            $client = (clone $clientQuery)->where('email', $invoice->to_email)->first();
            if ($client) {
                return $client->id;
            }
        }

        if (!empty($invoice->to_name)) {
            $client = (clone $clientQuery)->where('name', $invoice->to_name)->first();
            if ($client) {
                return $client->id;
            }
        }

        return null;
    }

    private function populateInvoiceFromClient(Internal_Invoices $invoice, Clients $client): void
    {
        $invoice->to_name = $client->name;
        $invoice->to_email = $client->email;
        $invoice->to_phone = $client->phone;
        $invoice->to_country = $client->country;
        $invoice->to_state = $client->state;
        $invoice->to_city = $client->city;
        $invoice->to_pincode = $client->pincode;
        $invoice->to_address = $client->address;
    }

    public function edit_invoice($id)
    {
        $user = $this->check_login();
        $this->set_timezone();
        if (!$this->userCanEditInvoices($user)) {
            return back()->with('error', 'You do not have permission to edit invoices.');
        }
        if ($user->user_type != 'admin' && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with('price_plan_expiry', 'Please renew or upgrade your subscription plan.');
        }

        $invoice = Internal_Invoices::with('items')->findOrFail($id);
        if ($invoice->type !== 'ar') {
            return redirect()->route('edit_invoice_ap', $invoice->id);
        }

        $this->authorizeInvoiceForUser($user, $invoice);
        $subscriber = $this->getInvoiceSubscriber($user);
        $clients = Clients::where('subscriber_id', $subscriber->id)->orderBy('name')->get();

        if (count($clients) < 1) {
            return back()->with('noclient', true);
        }

        $selectedClientId = $this->resolveInvoiceClientId($invoice, $subscriber);
        if ($selectedClientId === null && !empty($invoice->to_name)) {
            $selectedClientId = $clients->first(function ($client) use ($invoice) {
                return strcasecmp(trim($client->name), trim((string) $invoice->to_name)) === 0;
            })?->id;
        }
        $page = 'invoices';
        $invSetting = Invoice_settings::forUser((int) $subscriber->id);
        $taxMeta = $this->invoiceSettingsTaxMeta($invSetting);

        return view('web.edit_invoice', array_merge(compact('clients', 'user', 'page', 'invoice', 'selectedClientId'), $taxMeta, [
            'invoiceNote' => trim((string) ($invoice->invoice_note ?? '')),
            'isLocked' => true,
        ]));
    }

    public function update_invoice(Request $request, $id)
    {
        $request->validate(array_merge(
            $this->invoiceItemService()->singleItemValidationRules(),
            [
            'client' => 'required|exists:clients,id',
            'tax' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:PartiallyPaid,UnPaid,Paid,Cancelled',
            'due_date' => 'required',
            'invoice_note' => 'nullable|string|max:5000',
            ]
        ));

        $user = Auth::user();
        $this->set_timezone();
        if (!$this->userCanEditInvoices($user)) {
            return back()->with('error', 'You do not have permission to edit invoices.');
        }

        $invoice = Internal_Invoices::with('items')->findOrFail($id);
        if ($invoice->type !== 'ar') {
            abort(404);
        }

        $this->authorizeInvoiceForUser($user, $invoice);
        $subscriber = $this->getInvoiceSubscriber($user);
        $client = Clients::findOrFail($request->client);
        if ((int) $client->subscriber_id !== (int) $subscriber->id) {
            return back()->withInput()->withErrors(['client' => 'Please select a valid client for this account.']);
        }

        $discountPercent = max(0, min(100, (float) ($invoice->discount ?? 0)));
        $taxPercent = max(0, min(100, (float) $request->input('tax')));
        $invoiceAmount = $this->invoiceItemService()->sumAmounts(
            $this->invoiceItemService()->normalizeRequestItems($request)
        );
        $subtotal = $invoiceAmount - ($invoiceAmount * ($discountPercent / 100));

        $this->populateInvoiceFromClient($invoice, $client);
        $invoice->user_id = $user->id;
        $invoice->discount = $discountPercent;
        $invoice->export_service_tax_exempt = $taxPercent == 0.0;
        $invoice->tax = $taxPercent;
        $invoice->total = max(0, round($subtotal + ($subtotal * ($taxPercent / 100)), 2));
        $invoice->status = $request->status;
        $invoice->due_date = $this->normalizeInvoiceDueDate($request->due_date);
        $invoice->invoice_note = trim((string) $request->input('invoice_note', ''));

        $auditService = app(InvoiceAuditService::class);
        $auditService->ensureCreatedAudit($invoice);
        $auditService->markUpdated($invoice, $user);
        $this->persistInvoiceItems($invoice, $request, (int) $request->client, (int) $invoice->id);
        $auditService->syncLegacyInvoiceIfPaid($invoice, $user);
        $auditService->logActivity(
            $user,
            $subscriber->id,
            'Invoice Updated',
            $user->name . ' updated invoice ' . $invoice->invoice_no . ' at ' . $request->local_time,
            $request->local_time
        );

        return redirect()->route('invoices')->with('invoice_updated', 'Invoice Updated Successfully !');
    }

    public function edit_invoice_ap($id)
    {
        $user = $this->check_login();
        $this->set_timezone();
        if (!$this->userCanEditInvoices($user)) {
            return back()->with('error', 'You do not have permission to edit invoices.');
        }
        if ($user->user_type != 'admin' && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with('price_plan_expiry', 'Please renew or upgrade your subscription plan.');
        }

        $invoice = Internal_Invoices::with('items')->findOrFail($id);
        if ($invoice->type !== 'ap') {
            return redirect()->route('edit_invoice', $invoice->id);
        }

        $this->authorizeInvoiceForUser($user, $invoice);
        $page = 'invoices';

        return view('web.edit_invoice_ap', compact('user', 'page', 'invoice') + [
            'invoiceNote' => trim((string) ($invoice->invoice_note ?? '')),
            'isLocked' => true,
        ]);
    }

    public function update_invoice_ap(Request $request, $id)
    {
        $request->validate(array_merge(
            $this->invoiceItemService()->validationRules(),
            [
            'invoice_vendor_id' => 'required|string|min:2|max:100',
            'vendor_name' => 'required|string|min:2|max:150',
            'amount' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'tax' => 'required|numeric|min:0|max:100',
            'total_to_pay' => 'required|numeric|min:0',
            'status' => 'required|in:PartiallyPaid,UnPaid,Paid,Cancelled',
            'due_date' => 'required',
            'upload_invoice' => 'nullable|file|mimes:pdf|max:10240',
            'invoice_note' => 'nullable|string|max:5000',
            ]
        ));

        $user = Auth::user();
        $this->set_timezone();
        if (!$this->userCanEditInvoices($user)) {
            return back()->with('error', 'You do not have permission to edit invoices.');
        }

        $invoice = Internal_Invoices::with('items')->findOrFail($id);
        if ($invoice->type !== 'ap') {
            abort(404);
        }

        $this->authorizeInvoiceForUser($user, $invoice);
        $subscriber = $this->getInvoiceSubscriber($user);

        $invoiceAmount = $this->invoiceItemService()->sumAmounts(
            $this->invoiceItemService()->normalizeRequestItems($request)
        );
        $discountPercent = max(0, min(100, (float) $request->discount));
        $taxPercent = max(0, min(100, (float) $request->tax));
        $subtotal = $invoiceAmount - ($invoiceAmount * ($discountPercent / 100));
        $calculatedTotal = max(0, $subtotal + ($subtotal * ($taxPercent / 100)));

        $invoice->invoice_no = trim((string) $request->invoice_vendor_id);
        $invoice->to_name = trim((string) $request->vendor_name);
        $invoice->discount = $discountPercent;
        $invoice->tax = $taxPercent;
        $invoice->total = (float) $request->total_to_pay > 0 ? (float) $request->total_to_pay : $calculatedTotal;
        $invoice->status = $request->status;
        $invoice->due_date = $this->normalizeInvoiceDueDate($request->due_date);
        $invoice->invoice_note = trim((string) $request->input('invoice_note', ''));

        if ($request->hasFile('upload_invoice')) {
            $pdfFile = $request->file('upload_invoice');
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $pdfFile->getClientOriginalName());
            $destinationPath = 'web_assets/users/user' . $subscriber->id . '/invoice_uploads';
            $pdfFile->move($destinationPath, $fileName);
            $invoice->uploaded_invoice = 'user' . $subscriber->id . '/invoice_uploads/' . $fileName;
        }

        $auditService = app(InvoiceAuditService::class);
        $auditService->ensureCreatedAudit($invoice);
        $auditService->markUpdated($invoice, $user);
        $this->persistInvoiceItems($invoice, $request);
        $auditService->syncLegacyInvoiceIfPaid($invoice, $user);
        $auditService->logActivity(
            $user,
            $subscriber->id,
            'Invoice Updated',
            $user->name . ' updated AP invoice ' . $invoice->invoice_no . ' at ' . $request->local_time,
            $request->local_time
        );

        return redirect()->route('invoice_payment_made')->with('invoice_updated', 'Invoice Updated Successfully !');
    }

    public function job_role($id = "")
    {
        $user = Auth::user();
        $this->set_timezone();
        $page = "job_role";
        $job_roles = Job_roles::where('user_id', '=', $user->id)->get();
        return view('web.job_role', compact('user', 'page', 'job_roles'));
    }

    public function add_job_role()
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user->user_type == "Subscriber") {
            $page = "job_role";
            return view('web.add_job_role', compact('user', 'page'));
        } else {
            return back();
        }
    }

    public function add_new_job_role(request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if (isset($request->id)) {
            $job_role = Job_roles::find($request->id);
            $this->validate(
                $request,
                [
                    'job_role' => 'required',
                ]
            );
            $job_role->job_role = $request['job_role'];
            $job_role->save();
            $activity = new Activities();
            $activity->subscriber_id = $user->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Job Role Updated";
            $activity->activity_detail = $user->name . " updates job role " . $request->job_role . " at " . $request->local_time;
            $activity->activity_icon = "job_icon.png";
            $activity->local_time = $request->local_time;
            $activity->save();
            return back()->with("job_updated", "Job updated successfully.");
        }
        $data = new Job_roles();
        $this->validate(
            $request,
            [
                'job_role' => 'required|string|max:255|unique:job_roles',
            ]
        );
        $data->user_id = $user->id;
        $data->job_role = $request['job_role'];
        $data->save();
        $activity = new Activities();
        $activity->subscriber_id = $user->id;
        $activity->user_id = $user->id;
        $activity->activity_name = "New Job Role Added";
        $activity->activity_detail = $user->name . " added new job role " . $request->job_role . " at " . $request->local_time;
        $activity->activity_icon = "job_icon.png";
        $activity->local_time = $request->local_time;
        $activity->save();
        return redirect()->route('job_role')->with('job_added', "Job role added successfully.");
    }

    public function delete_job_role($id = null, $localtime = null)
    {
        if (!empty($id)) { //edit the page.
            $user = Auth::user();
            $this->set_timezone();
            if ($user) {
                $job_role = Job_roles::find($id);
                $job_role->delete();
                $activity = new Activities();
                $activity->subscriber_id = $user->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Job Role Deleted";
                $activity->activity_detail = $user->name . " deleted job role " . $job_role->job_role . " at " . $localtime;
                $activity->activity_icon = "job_icon.png";
                $activity->local_time = $localtime;
                $activity->save();
                return back()->with('deleted', 'Job deleted successfully.');
            } else {
                return redirect()->route('login');
            }
        } else { //view the page.
            return back();
        }
    }

    public function aboutadvisori()
    {
        $user = Auth::user();
        $this->set_timezone();
        $about_adwiseri = About_Advisori::first();
        $page = "about_adwiseri";
        return view('web.aboutadvisori', compact('user', 'about_adwiseri', 'page'));
    }

    public function contactus()
    {
        $user = Auth::user();
        $this->set_timezone();
        $contact = Contactus::first();
        $countries = Countries::get();
        $page = "contact_us";
        return view('web.contactus', compact('user', 'contact', 'countries', 'page'));
    }

    public function post_contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:100',
            'phone' => 'required|phone_intl',
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $maildata = new \stdClass();
        $maildata->name = $request['name'];
        $maildata->email = $request['email'];
        $maildata->phone = $request['phone'];
        $maildata->country = $request['country'];
        $maildata->city = $request['city'];
        $maildata->message = $request['message'];
        $maildata->contact = "True";
        foreach (BrandedMail::adminNotificationRecipients() as $recipient) {
            Mail::to($recipient)->send(new EmailVerification($maildata));
        }
        if (Mail::failures()) {
            echo 'Sorry! Please try again latter';
        } else {
            echo 'Success';
            return redirect()->route('contactus')->with('message_sent', 'Your message was sent successfully.');
        }
    }

    public function privacy_policy()
    {
        $user = Auth::user();
        $this->set_timezone();
        $page = "privacy_policy";
        return view('web.privacy_policy', compact('user', 'page'));
    }

    public function terms_conditions()
    {
        $user = Auth::user();
        $page = "terms_conditions";
        return view('web.terms_conditions', compact('user', 'page'));
    }

    public function terms_use()
    {
        $user = Auth::user();
        $page = "terms_use";
        return view('web.terms_use', compact('user', 'page'));
    }

    public function refund_policy()
    {
        $user = Auth::user();
        $page = "refund_policy";
        return view('web.refund_policy', compact('user', 'page'));
    }

    public function support()
    {
        $user = $this->check_login();
        if ($user->type_user != "affiliate" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user) {
            $type =  ($user->type_user == 'affiliate') ? 'Affiliates' : Null;
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            $faqs = Faq::where('type', $type)->get();
            $page = "support";
            if ($user->type_user == 'affiliate') {

                return view('affiliate.support', compact('user', 'page', 'faqs', 'roles'));
            } else {
                return view('web.support', compact('user', 'page', 'faqs', 'roles'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function ask_support()
    {
        $user = $this->check_login();
        $this->set_timezone();
        if ($user->type_user != "affiliate" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user) {
            if ($user->user_type == "Subscriber" || $user->user_type == "Affiliate") {
                $subscriber = $user;
                $tickets = Tickets::with(['user:id,name', 'subscriber:id,name'])
                    ->where('subscriber_id', '=', $subscriber->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $subscriber = User::find($user->added_by);
                $tickets = Tickets::with(['user:id,name', 'subscriber:id,name'])
                    ->where('user_id', '=', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            $page = "support";
            if ($user->type_user == 'affiliate') {

                return view('affiliate.ask_support', compact('user', 'page', 'clients', 'subscriber', 'tickets'));
            } else {

                return view('web.ask_support', compact('user', 'page', 'clients', 'subscriber', 'tickets'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function ask_new_question(Request $request)
    {
        function ticket()
        {
            $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $tic = "";
            for ($i = 0; $i < 8; $i++) {
                $tic = $tic . $str[rand(0, strlen($str) - 1)];
            }
            return $tic;
        }
        $user = Auth::user();
        if (!$user) {
            $user = auth()->guard('affiliates')->user();
            $user = User::where('email', $user->email)->first();
            $user['type_user'] = 'affiliate';
        }

        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Affiliate") {
                $subscriber = $user;
            } else {
                $subscriber =  empty($user->added_by) ? User::find($user->id) : User::find($user->added_by);
            }
            $this->validate($request, [
                'support' => 'required',
                'question' => 'required',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png|max:4096'
            ], [
                'attachment.mimes' => 'Please select a valid file format (jpg, jpeg, png).',
                'attachment.max' => 'Please select file up to 4MB.'
            ]);
            $data = new Tickets();
            $data->ticket_no = ticket();
            $data->user_id = $user->id;
            $data->subscriber_id = $subscriber->id;
            // $data->client_id = $request['client'];
            $data->issue = $request['question'];
            $data->status = "Open";
            $data->support = $request['support'];
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . rand(100, 999) . "." . $extension;
                $file->move('web_assets/users/ticket_images/', $filename);
                $data->attachment = $filename;
            }
            $data->save();

            app(\App\Services\TicketActivityService::class)->logCreation($data, $user);

            app(\App\Services\NotificationService::class)->notifyAudience(
                'admin',
                'support_tickets',
                'New support ticket #' . $data->ticket_no,
                'A new support ticket has been raised: ' . \Illuminate\Support\Str::limit($data->issue, 120),
                route('manage_support')
            );

            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Query Ticket Sent";
            if ($user->user_type == "Subscriber") {
                $activity->activity_detail = "Ticket raised by " . $user->name . " at " . $request->local_time;
            } else {
                $activity->activity_detail = "Ticket raised by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
            }
            $activity->activity_icon = "user.png";
            $activity->local_time = $request->local_time;
            $activity->save();

            $maildata = new \stdClass();
            $maildata->ticket_id = $data['ticket_no'];
            $maildata->subscriber_id = $data['subscriber_id'];
            $maildata->support = $data['support'];
            $maildata->department = $data['support'];
            $maildata->ticket_raiser = $subscriber->name . ' (' . $subscriber->id . ') - ' . $user->name . ' (' . $user->id . ')';
            $maildata->date = $data['created_at'];
            $maildata->issue = $data['issue'];
            $maildata->attachment = $data['attachment'];
            $maildata->attachment_label = $data['attachment'] ? ('Attached (' . $data['attachment'] . ')') : 'No attachment';
            $maildata->contact = "True";
            foreach (BrandedMail::adminNotificationRecipients() as $recipient) {
                Mail::to($recipient)->send(new SupportMail($maildata));
            }
            if (Mail::failures()) {
                echo 'Sorry! Please try again latter';
            } else {
                echo 'Success';
            }
            if ($user->type_user == 'Affiliate') {
                return redirect()->route('ask_support')->with('success', 'Your support request was sent successfully.');
            } else {
                return redirect()->route('ask_support_affiliate')->with('success', 'Your support request was sent successfully.');
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function my_query($id)
    {
        $user = Auth::user() ?? Auth::guard('affiliates')->user()->user;

        if ($user) {
            if ($id) {
                $query = Tickets::find($id);
                $page = "support";
                $template = ($user->user_type != 'Affiliate') ?  'web.my_query' :'affiliate.my_query';

                return view($template, compact('user', 'page', 'query'));
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function sub_reports()
    {
        $user = $this->check_login();
        $ccService = app(CountryCategorySettingsService::class);
        $subscriber = $ccService->resolveSubscriber($user);

        if ($user->user_type != "admin" && membership_access_blocked_for_subscriber($subscriber)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        if ($user->user_type != 'admin') {
            $reportRoles = UserRoles::where('user_id', $user->id)->where('module', 'Reports')->first();
            if (!$reportRoles || ($reportRoles->read_only != 1 && $reportRoles->read_write_only != 1)) {
                return redirect()->route('client')->with('access_denied', 'You do not have access to Reports.');
            }
        }

        $this->set_timezone();

        if ($subscriber->category == "Law Firm") {
            $client_jobs = Client_jobs::where('category', '=', $subscriber->category)->get();
        } elseif ($ccService->isTravelAgentSubscriber($subscriber)) {
            $client_jobs = $ccService->getClientJobsForSubscriber($subscriber);
        } else {
            $client_jobs = Client_jobs::where('category', '=', $subscriber->category)->where('sub_category', '=', $subscriber->sub_category)->get();
        }
        $visa_categories = Subscriber_Sub_Categories::where('category_name', '=', 'Visas & Immigration Advisory')->get();
        $law_categories = Subscriber_Sub_Categories::where('category_name', '=', 'Law Firm')->get();
        $travel_categories = Countries::get();
        $total_apps = array();
        $applications = Applications::where('subscriber_id', '=', $subscriber->id)->get();

        foreach ($client_jobs as $job) {
            $categ = $job->job;
            $categ_app = 0;
            foreach ($applications as $app) {
                if ($categ == $app->application_name) {
                    $categ_app += 1;
                }
            }
            $total_apps[$categ] = $categ_app;
        }
        $internal_invoices = Internal_Invoices::where('subscriber_id', '=', $subscriber->id)->get();
        $internal_total = 0;
        foreach ($internal_invoices as $inv) {
            $internal_total += $inv->total;
        }
        $unpaid = Internal_Invoices::where('subscriber_id', '=', $subscriber->id)->where('status', '=', 'UnPaid')->get();
        $unpaid_total = 0;
        foreach ($unpaid as $inv) {
            $unpaid_total += $inv->total;
        }
        $invoices = Invoices::where('user_id', '=', $subscriber->id)->get();
        $total = 0;
        foreach ($invoices as $inv) {
            $total += $inv->total;
        }
        $total_invoices = count($internal_invoices);
        $total_paid = $total_invoices - count($unpaid);
        $total_unpaid = count($unpaid);
        $total_amt = $internal_total;
        $paid_total = $total_amt - $unpaid_total;
        $page = "reports";

        $price_plans = Membership::orderBy('created_at', 'asc')->get();
        $reportModuleAvailability = \App\Support\ModuleAvailability::reportModules($user);


        return view('web.reports', compact('user', 'total_apps', 'page', 'applications', 'total_invoices', 'total_paid', 'total_unpaid', 'total_amt', 'paid_total', 'unpaid_total', 'price_plans', 'reportModuleAvailability'));
    }

    public function sub_reports_support_tickets()
    {
        $user = $this->check_login();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->set_timezone();
        $subscriberId = ($user->user_type == "Subscriber" || $user->user_type == "admin") ? $user->id : $user->added_by;

        $query = Tickets::with(['subscriber:id,name', 'client:id,name'])->orderBy('created_at', 'desc');
        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        $startDate = $this->normalizeDateValue(request('startdate'));
        $endDate = $this->normalizeDateValue(request('enddate'));
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('subscriber', function ($row) {
                return $row->subscriber ? $row->subscriber->name . '(' . $row->subscriber_id . ')' : '';
            })
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name . '(' . $row->client_id . ')' : '';
            })
            ->editColumn('status', function ($row) {
                return $row->status;
            })
            ->editColumn('issue', function ($row) {
                $issue = is_string($row->issue) ? $row->issue : '';
                $text = htmlspecialchars($issue);
                $words = explode(' ', $text);
                $truncated = implode(' ', array_slice($words, 0, 25));
                $previewText = count($words) > 25 ? $truncated . '...' : $truncated;

                return '<div class="message-tooltip" data-full-text="' . htmlspecialchars($text) . '">
                            <span class="hover-expand">' . $previewText . '</span>
                        </div>';
            })
            ->editColumn('created_at', function ($row) {
                return date("d-m-Y H:i:s", strtotime($row->created_at));
            })
            ->rawColumns(['issue'])
            ->make(true);
    }

    public function sub_reports_activity_log()
    {
        $user = $this->check_login();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->set_timezone();
        $subscriberId = ($user->user_type == "Subscriber" || $user->user_type == "admin") ? $user->id : $user->added_by;

        $query = Activities::with(['user:id,name'])->orderBy('created_at', 'desc');
        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        $startDate = $this->normalizeDateValue(request('startdate'));
        $endDate = $this->normalizeDateValue(request('enddate'));
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                if (!empty($row->user_name)) {
                    return $row->user_name;
                }

                return $row->user ? $row->user->name : '';
            })
            ->editColumn('created_at', function ($row) {
                return date("d-m-Y H:i:s", strtotime($row->created_at));
            })
            ->make(true);
    }

    public function communications()
    {
        $user = $this->check_login();
        if ($user->user_type != "admin" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $siteusers = User::where('added_by', '=', $subscriber->id)->get();
            } else {
                $subscriber = User::find($user->added_by);
                $siteusers = false;
            }


            $notificationService = app(\App\Services\NotificationService::class);
            $messages = $notificationService->messagesVisibleToUser($user);
            $page = "communications";
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            $unreadMessageCount = $notificationService->envelopeCount($user);
            if (request()->ajax()) {
                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

                $communication_roles = null;
                if ($user->user_type != "admin") {
                    $communication_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Communication')->first();
                }
                $messages = $messages->whereBetween('created_at', [$startDate, $endDate]);

                return DataTables::of($messages)
                    ->addIndexColumn()
                    ->addColumn('status', function ($row) use ($user, $notificationService) {
                        return $notificationService->messageStatusBadgeHtml($user, $row);
                    })
                    ->addColumn('recevier_name', function ($row) use ($user) {
                        if ($row->send_by == 1) {
                            $receiver = $user->name;
                        } else {
                            $receiver = "";
                            $receivernames = json_decode($row->receiver_name, true);
                            foreach ($receivernames as $k => $name) {
                                if ($k == count($receivernames) - 1) {
                                    $receiver = $receiver . $name;
                                } else {
                                    $receiver = $receiver . $name . ", ";
                                }
                            }
                        }
                        if (strlen($receiver) > 22) {
                            return substr($receiver, 0, 22) . "...";
                        } else {
                            return $receiver;
                        }
                    })
                    ->editColumn('created_at', function ($row) {
                        return date("d-m-Y H:i:s", strtotime($row->created_at));
                    })
                    ->editColumn('message', function ($row) {
                        if (strlen($row->message) > 22) {
                            return substr($row->message, 0, 22) . '...';
                        } else {
                            return $row->message;
                        }
                    })
                    ->addColumn('action', function ($row) use ($communication_roles, $user, $notificationService) {
                        $canView = $user->user_type == "admin" || ($communication_roles && ($communication_roles->read_only == 1 || $communication_roles->read_write_only == 1));
                        $canDelete = $user->user_type == "admin"
                            || ($communication_roles && ($communication_roles->write_only == 1 || $communication_roles->read_write_only == 1))
                            || ((int) $row->send_by === (int) $user->id);
                        $status = $notificationService->messageStatusForUser($user, $row);
                        $viewHref = $canView ? route('view_message', $row->id) : '#';

                        $html = '<div class="comm-action-btns">';
                        $html .= $canView
                            ? '<a href="' . $viewHref . '" class="comm-action-btn comm-view-btn" title="View"><i class="fa-solid fa-eye"></i></a>'
                            : '<span class="comm-action-btn comm-action-btn--disabled" title="View not allowed"><i class="fa-solid fa-eye"></i></span>';

                        if ($status === 'unread') {
                            $html .= '<button type="button" class="comm-action-btn comm-mark-read-btn js-mark-message-read" title="Mark as read" data-id="' . $row->id . '"><i class="fa-solid fa-envelope-open-text"></i></button>';
                        } else {
                            $html .= '<span class="comm-action-btn comm-action-btn--muted" title="Already read / sent"><i class="fa-solid fa-envelope-open-text"></i></span>';
                        }

                        if ($canDelete) {
                            $html .= '<button type="button" class="comm-action-btn comm-delete-btn js-delete-message" title="Delete" data-id="' . $row->id . '"><i class="fa-solid fa-trash"></i></button>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->setRowClass(function ($row) use ($user, $notificationService) {
                        return $notificationService->messageStatusForUser($user, $row) === 'unread' ? 'comm-row-unread' : '';
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }
            return view('web.communications', compact('user', 'page', 'messages', 'siteusers', 'roles', 'notificationService', 'unreadMessageCount'));
        } else {
            return redirect()->route('login');
        }
    }

    public function messaging()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $siteusers = User::where('added_by', '=', $subscriber->id)->get();
            } else {
                $subscriber = User::find($user->added_by);
                $siteusers = false;
            }
            $page = "messaging";
            return view('web.messaging', compact('user', 'page', 'siteusers'));
        } else {
            return redirect()->route('login');
        }
    }

    public function email_broadcast()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $staffMembers = User::where('added_by', $subscriber->id)->where('user_type', 'User')->orderBy('name')->get();
            $clients = Clients::where('subscriber_id', $subscriber->id)->orderBy('name')->get();
        } else {
            $subscriber = User::find($user->added_by);
            $staffMembers = User::where('added_by', $subscriber->id)->where('user_type', 'User')->orderBy('name')->get();
            $clients = Clients::where('user_id', $user->id)->orderBy('name')->get();
        }

        $page = "email_broadcast";
        $subscriberFooter = \App\Support\BrandedMail::subscriberFooterContext($subscriber);
        $broadcastLimits = [
            'chunk_size' => (int) config('mail.broadcast_chunk_size', 25),
            'chunk_delay_seconds' => (int) config('mail.broadcast_chunk_delay_seconds', 2),
            'subject_max' => 200,
            'body_max' => 50000,
        ];

        return view('web.email_broadcast', compact('user', 'page', 'staffMembers', 'clients', 'subscriber', 'subscriberFooter', 'broadcastLimits'));
    }

    public function send_email_broadcast(Request $request, EmailBroadcastService $emailBroadcastService)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $communicationRoles = UserRoles::where('user_id', $user->id)->where('module', 'Communication')->first();
        $canSend = $user->user_type === 'Subscriber'
            || ($communicationRoles && ($communicationRoles->write_only == 1 || $communicationRoles->read_write_only == 1));

        if (!$canSend) {
            return back()->with('broadcast_error', 'You do not have permission to send email broadcasts.');
        }

        $this->validate($request, [
            'communicate_type' => 'required|in:internal,external',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'subject' => 'required|string|max:200',
            'body' => 'required|string|min:3|max:50000',
        ]);

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
            $staffUserId = null;
        } else {
            $subscriber = User::find($user->added_by);
            $staffUserId = $user->id;
        }

        $selectedRecipients = array_values(array_unique($request->recipients));
        $communicateType = $request->communicate_type;

        if ($communicateType === 'internal') {
            $hasStaff = User::where('added_by', $subscriber->id)->where('user_type', 'User')->exists();
            if (!$hasStaff) {
                return back()->with('no_broadcast_recipients', 'No clients found in the system')->withInput();
            }
            $resolvedRecipients = $emailBroadcastService->resolveStaffRecipients($subscriber->id, $selectedRecipients);
        } else {
            $clientQuery = Clients::where('subscriber_id', $subscriber->id);
            if ($staffUserId) {
                $clientQuery->where('user_id', $staffUserId);
            }
            if (!$clientQuery->exists()) {
                return back()->with('no_broadcast_recipients', 'No clients found in the system')->withInput();
            }
            $resolvedRecipients = $emailBroadcastService->resolveClientRecipients($subscriber->id, $staffUserId, $selectedRecipients);
        }

        $result = $emailBroadcastService->queueBroadcast(
            $user,
            $communicateType,
            $request->subject,
            $request->body,
            $resolvedRecipients,
            $subscriber->id,
            $selectedRecipients
        );

        if (!empty($result['error'])) {
            return back()->with('broadcast_error', $result['error'])->withInput();
        }

        if (empty($result['queued'])) {
            return back()->with('broadcast_error', 'Unable to queue email broadcast. Please try again.')->withInput();
        }

        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "Email Broadcast";
        $activity->activity_detail = "Email broadcast queued by " . $user->name . " for " . $result['total_recipients'] . " recipient(s) at " . $request->local_time;
        $activity->activity_icon = "communication.png";
        $activity->local_time = $request->local_time;
        $activity->save();

        $message = 'Email broadcast queued successfully for ' . $result['total_recipients'] . ' recipient(s). '
            . 'Emails will be sent in the background (Ref: ' . $result['broadcast_id'] . ').';

        return back()->with('broadcast_sent', $message);
    }

    public function upload_email_broadcast_image(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $communicationRoles = UserRoles::where('user_id', $user->id)->where('module', 'Communication')->first();
        $canSend = $user->user_type === 'Subscriber'
            || ($communicationRoles && ($communicationRoles->write_only == 1 || $communicationRoles->read_write_only == 1));

        if (!$canSend) {
            return response()->json(['message' => 'You do not have permission to upload broadcast images.'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
        ]);

        if ($user->user_type === 'Subscriber') {
            $subscriberId = $user->id;
        } else {
            $subscriberId = (int) $user->added_by;
        }

        $directory = public_path('web_assets/users/user' . $subscriberId . '/broadcast');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return response()->json(['message' => 'Unable to prepare image storage.'], 500);
        }

        $file = $request->file('image');
        $filename = 'broadcast_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $relativePath = 'web_assets/users/user' . $subscriberId . '/broadcast/' . $filename;

        return response()->json([
            'url' => url($relativePath),
        ]);
    }

    public function communicate(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $this->validate($request, [
                'sendto' => 'required',
                'message' => 'required|string',
            ]);

            // Function to generate a unique communication ID
            function communication_id()
            {
                $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $id = "";
                for ($i = 0; $i < 7; $i++) {
                    $id = $id . $ch[rand(0, strlen($ch) - 1)];
                }
                if (Internal_communications::where('communication_id', '=', $id)->first()) {
                    return communication_id();
                }
                return $id;
            }

            $communication_id = communication_id();
            $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);
            $offerBenefitService = app(OfferBenefitService::class);
            $membership_plan = Membership::where('plan_name', '=', $subscriber->membership)->first();

            if (!$membership_plan) {
                return back()->with('error', 'Invalid membership plan.');
            }

            if (!$offerBenefitService->canSendMessage($subscriber)) {
                return back()->with('error', 'You have exceeded your subscription message limit.');
            }

            // Proceed to send the message
            $sendto = $request->sendto;
            $receiver_id = array();
            $receiver_name = array();
            // dd(  $sendto );
            if ($sendto != null) {
                if (count($sendto)) {
                    // Subscribers can only message their own staff and/or clients (no admin recipients)
                    $sendto = array_values(array_filter($sendto, function ($recipient) {
                        return $recipient !== 'admin';
                    }));

                    if (in_array('all user', $sendto)) {
                        $siteusers = User::where('added_by', '=', $subscriber->id)->get();
                        foreach ($siteusers as $suser) {
                            array_push($receiver_id, $suser->id);
                            array_push($receiver_name, $suser->name);
                        }
                        $all_user_index = array_search('all user', $sendto);
                        array_splice($sendto, $all_user_index, 1);
                    } else {
                        if (count($sendto)) {
                            foreach ($sendto as $uid) {
                                $suser = User::find($uid);
                                array_push($receiver_id, $suser->id);
                                array_push($receiver_name, $suser->name);
                            }
                        }
                    }

                    // Save the message
                    $message = new Internal_communications();
                    $message->subscriber_id = $subscriber->id;
                    $message->communication_id = $communication_id;
                    $message->user_id = $user->id;
                    $message->send_by = $user->id;
                    $message->send_to = json_encode($receiver_id, true);
                    $message->sender_name = $user->name;
                    $message->receiver_name = json_encode($receiver_name, true);
                    $message->message = $request['message'];
                    $message->save();
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id ;
                    $activity->user_id = $user->id;
                    $activity->user_name =  $user->name;
                    $activity->activity_name = "Message Sent";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = "Message sent by " .  $user->name . " at " . $request->local_time;
                    } else {
                        $activity->activity_detail = "Message sent by " .  $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                    }
                    $activity->activity_icon = "mail.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();

                    return back()->with('sent', 'Message sent successfully.');
                } else {
                    return back()->with('noUser', 'No user selected.');
                }
            } else {
                return back()->with('noUser', 'No user selected.');
            }
        } else {
            return redirect()->route('login');
        }
    }


    public function view_message($id = null)
    {
        $user = Auth::user();
        if ($id) {
            $page = "communications";
            $message = Internal_communications::find($id);
            if ($message && $user) {
                app(\App\Services\NotificationService::class)->markMessageRead($user, (int) $message->id);
            }
            return view('web.view_message', compact('message', 'user', 'page'));
        }
    }

    public function client_discussion()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $discussions = Client_discussions::with(['user', 'application'])
                    ->where('subscriber_id', '=', $subscriber->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $clients = Clients::where('subscriber_id', '=', $subscriber->id)
                    ->whereHas('applications')
                    ->get();
            } else {
                $subscriber = User::find($user->added_by);
                $discussions = Client_discussions::with(['user', 'application'])
                    ->where('user_id', '=', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $clients = Clients::where('user_id', '=', $user->id)
                    ->whereHas('applications')
                    ->get();
            }
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            $visibility = app(ApplicationVisibilityService::class);
            $applications = $visibility->queryForUser($user, $subscriber)->get();
            $page = "communications";
            $meetingModeFilters = TableFilterCountService::countBy(
                $discussions,
                fn ($discussion) => $discussion->communication_type
            );
            return view('web.client_discussion', compact('user', 'roles', 'page', 'discussions', 'subscriber', 'clients', 'applications', 'meetingModeFilters'));
        } else {
            return redirect()->route('login');
        }
    }

    public function post_client_discussion(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if ($request) {
                $client = Clients::find($request->client);
                $communicationDate = $this->normalizeDateTimeValue($request->input('communication_date'));
                if (!$communicationDate) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['communication_date' => 'Please provide a valid communication date.']);
                }

                $discussion = new Client_discussions();
                $discussion->subscriber_id = $subscriber->id;
                $discussion->user_id = $user->id;
                $discussion->user_name = $user->name;
                $discussion->client_id = $request['client'];
                $discussion->client_name = $client->name;
                $discussion->application_id = $request['application'];
                $discussion->communication_type = $request['communication_type'];
                $discussion->communication_date = $communicationDate;
                $discussion->discussion = $request['discussion'];
                $discussion->save();
                return redirect()->back()->with('disucssion_saved', 'Discussion saved successfully.');
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function user_applications()
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user) {
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
                $assignments = Application_assignments::whereNotNull('application_id')
                    ->whereHas('application')
                    ->where('subscriber_id', '=', $subscriber->id)
                    ->with(['client', 'application', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $subscriber = User::find($user->added_by);
                $assignments = Application_assignments::whereNotNull('application_id')
                    ->whereHas('application')
                    ->where('subscriber_id', '=', $subscriber->id)
                    ->where('user_id', '=', $user->id)
                    ->with(['client', 'application', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            $userApplicationFilters = TableFilterCountService::countBy(
                $assignments,
                function ($assignment) {
                    $assignedUser = $assignment->user;

                    return $assignedUser
                        ? $assignedUser->name
                        : trim((string) ($assignment->user_name ?: ('User #' . $assignment->user_id)));
                }
            );
            $clients = Clients::where('subscriber_id', '=', $subscriber->id)
                ->whereHas('applications', function ($query) {
                    $query->whereNull('assign_to');
                })
                ->orderBy('name')
                ->get();
            $siteusers = User::where('designation', 'Consultant/Advisor')
                ->where('added_by', '=', $subscriber->id)
                ->orderBy('name')
                ->get();
            $visibility = app(ApplicationVisibilityService::class);
            $applications = $visibility->queryForUser($user, $subscriber)->get();
            $unassignedApplicationsCount = $visibility->hasSubscriberLevelApplicationsAccess($user)
                ? Applications::where('subscriber_id', '=', $subscriber->id)
                    ->where(function ($query) {
                        $query->whereNull('assign_to')->orWhere('assign_to', '');
                    })
                    ->count()
                : 0;
            $page = "applications";
            return view('web.user_applications', compact('roles', 'assignments', 'user', 'page', 'clients', 'siteusers', 'applications', 'unassignedApplicationsCount', 'userApplicationFilters'));
        } else {
            return redirect()->route('login');
        }
    }

    public function update_application_assignment($id)
    {
        $user = Auth::user();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $assignment = Application_assignments::find($id);
        if (!$assignment) {
            return redirect()->route('user_applications');
        }

        $client = Clients::find($assignment->client_id);
        if (!$client) {
            return redirect()->route('user_applications');
        }

        $subscriberId = $assignment->subscriber_id ?: ($client->subscriber_id ?? null);

        // Only clients that already have an assignment to an advisor/counsellor
        $assignedClientIds = Application_assignments::when($subscriberId, function ($query) use ($subscriberId) {
                return $query->where('subscriber_id', $subscriberId);
            })
            ->whereNotNull('user_id')
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        if ($client && !in_array($client->id, $assignedClientIds, true)) {
            $assignedClientIds[] = $client->id;
        }

        $clients = Clients::whereIn('id', $assignedClientIds)->orderBy('name')->get();

        // Only applications already assigned to an advisor/counsellor for the selected client
        $assignedApplicationIds = Application_assignments::where('client_id', $assignment->client_id)
            ->whereNotNull('application_id')
            ->pluck('application_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        if ($assignment->application_id && !in_array($assignment->application_id, $assignedApplicationIds, false)) {
            $assignedApplicationIds[] = $assignment->application_id;
        }

        $applications = Applications::where('client_id', $assignment->client_id)
            ->where(function ($query) use ($assignedApplicationIds) {
                $query->whereNotNull('assign_to');
                if (!empty($assignedApplicationIds)) {
                    $query->orWhereIn('application_id', $assignedApplicationIds);
                }
            })
            ->orderBy('application_name')
            ->get();

        $advisors = User::where('designation', 'Consultant/Advisor')
            ->where('added_by', '=', $client->subscriber_id)
            ->orderBy('name')
            ->get();
        $page = "applications";
        return view('web.update_application_assignment', compact('assignment', 'user', 'advisors', 'page', 'client', 'clients', 'applications'));
    }

    public function user_app_assignment(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if (membership_access_blocked($user)) {
                return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
            }
            $assignment = Application_assignments::find($request->id);
            if ($assignment) {
                $u = User::find($request->user_id);
                if (!$u) {
                    return redirect()->back()->withInput()->withErrors(['user_id' => 'Please select a valid User/Advisor.']);
                }
                // Client & application stay locked; only reassign advisor/counsellor
                $assignment->subscriber_id = $u->added_by ?: $assignment->subscriber_id;
                $assignment->user_id = $request['user_id'];
                $assignment->user_name = $u->name;
                $assignment->save();
                $app = Applications::where('application_id', '=', $assignment->application_id)->first();
                if ($app) {
                    $app->assign_to = $u->id;
                    $app->save();
                }
                app(\App\Services\OperationalNotificationService::class)
                    ->notifyApplicationAssigned($u, $app, Clients::find($assignment->client_id));
                $activity = new Activities();
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Application Assign Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "Application Assign updated by " . $user->name . " at " . $request->local_time;
                } else {
                    $activity->activity_detail = "Application Assign updated by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                return redirect()->route('user_applications')->with('assignment_updated', "Application assignment updated.");
            } else {
                $client = Clients::find($request->client_id);
                if ($client) {
                    $assignment = new Application_assignments();
                    $u = User::find($request->user_id);
                    $assignment->client_id = $request['client_id'];
                    $assignment->application_id = $request['application_id'];
                    $assignment->user_id = $request['user_id'];
                    $assignment->subscriber_id = $u->added_by;
                    $assignment->user_name = $u->name;
                    $assignment->save();
                    $app = Applications::where('application_id', '=', $request['application_id'])->first();
                    $app->assign_to = $u->id;
                    $app->save();
                    app(\App\Services\OperationalNotificationService::class)
                        ->notifyApplicationAssigned($u, $app, $client);
                    $activity = new Activities();
                    $activity->client_id = $client->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->activity_name = "Assignment Added";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = "New Assignment added by " . $user->name . " at " . $request->local_time;
                    } else {
                        $activity->activity_detail = "New Assignment added by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                    }
                    $activity->activity_icon = "user.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();
                    return redirect()->route('user_applications')->with('assignment_added', "Application assigned successfully.");
                } else {
                    return back();
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function client_documents()
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user->user_type != "admin" && membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        if ($user) {
            $roles = UserRoles::where('user_id', '=', $user->id)->first();
            if ($user->user_type != "admin" && membership_access_blocked($user)) {
                return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
            }
            if ($user->user_type == "Subscriber" || $user->user_type == "admin") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if ($user->user_type == 'admin') {

                $applications = Applications::get();
                $clients = Clients::get();
            } else {
                $visibility = app(ApplicationVisibilityService::class);
                $applications = $visibility->queryForUser($user, $subscriber)->get();
                $clients = Clients::where('subscriber_id', '=', $subscriber->id)->get();
            }
            $page = "applications";
            if ($user->user_type == "admin") {
                $client_docs = Client_Docs::whereNotNull('application_id')->orderBy('created_at', 'desc')->get();
            } else {
                $visibility = app(ApplicationVisibilityService::class);
                $visibleApplicationIds = $visibility->visibleApplicationReferenceIds($user, $subscriber);

                $client_docs = Client_Docs::whereNotNull('application_id')
                    ->whereHas('application')
                    ->where('user_id', '=', $subscriber->id)
                    ->when(
                        $user->user_type === 'User' && !$visibility->hasSubscriberLevelApplicationsAccess($user),
                        fn ($query) => $query->whereIn('application_id', $visibleApplicationIds)
                    )
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            if (request()->ajax()) {
                $application_roles = null;
                if ($user->user_type != "admin") {

                    $application_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Applications')->first();
                }

                $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
                $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

                $client_docs = $client_docs->whereBetween('created_at', [$startDate, $endDate]);
                return DataTables::of($client_docs)
                    ->editColumn('created_at', function ($row) {
                        return date("d-m-Y H:i:s", strtotime($row->created_at));
                    })
                    ->addColumn('action', function ($row) {
                        $html = '<a style="background:transparent;border:none;" class="p-0 m-0 text-dark" href=' . route('application_view', $row->id) . '><i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a>';
                        return $html;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('web.client_documents', compact('roles', 'applications', 'user', 'page', 'clients', 'client_docs'));
        } else {
            return redirect()->route('login');
        }
    }

    public function client_document_update($id)
    {
        $user = $this->check_login();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $document = Client_docs::find($id);
            if (!$document) {
                return redirect()->route('client_documents');
            }

            $application = Applications::where('application_id', $document->application_id)->first();
            if ($application && !app(ApplicationVisibilityService::class)->canViewApplication($user, $application)) {
                return redirect()->route('client_documents');
            }

            $clients = Clients::get();
            $page = "applications";
            return view('web.client_document_update', compact('document', 'user', 'page', 'clients', 'application'));
        } else {
            return redirect()->route('login');
        }
    }

    public function upload_client_document(Request $request)
    {
        $user = Auth::user();
        $this->set_timezone();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            if (membership_access_blocked($user)) {
                return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
            }
            $document = Client_Docs::find($request->id);
            $docFileRule = $document ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096' : 'required|file|mimes:jpg,jpeg,png,pdf|max:4096';
            $ccService = app(\App\Services\CountryCategorySettingsService::class);
            $this->validate($request, [
                'doc_file' => $docFileRule,
                'doc_type' => 'required|string|max:100',
                'doc_name' => 'required|string|min:3|max:100',
                'doc_folder' => 'required|string|max:120',
            ], [
                'doc_file.mimes' => 'Please select a valid file format (jpg, jpeg, png, pdf).',
                'doc_file.max' => 'Please select file up to 4MB.',
            ]);
            $docFolders = \App\Support\ApplicationDocumentFolders::resolveForUpload(
                $ccService,
                $request->doc_folder,
                $request->doc_type
            );
            $docFolder = $docFolders[0] ?? 'Other';
            if ($document) {
                $client = Clients::find($request->client_id);
                $application = Applications::find($request->application_id);
                $subscriber = User::find($client->subscriber_id);
                $document->client_id = $request['client_id'];
                $document->application_id = $application->application_id;
                $document->user_id = $subscriber->id;
                $document->doc_type = $request['doc_type'];
                $document->doc_name = $request['doc_name'];
                $document->doc_folder = $docFolder;
                $document->doc_folders = $docFolders;
                if ($request->hasFile('doc_file')) {
                    $file = $request->file('doc_file');
                    $filename = \App\Support\DocumentFileName::storageName($request->doc_name, $file->getClientOriginalName());
                    $file->move('web_assets/users/client' . $document->client_id . '/docs/', $filename);
                    $document->doc_file = $filename;
                }
                $document->save();
                $activity = new Activities();
                $activity->subscriber_id = $subscriber->id;
                $activity->user_id = $user->id;
                $activity->user_name = $user->name;
                $activity->activity_name = "Document Updated";
                if ($user->user_type == "Subscriber") {
                    $activity->activity_detail = "Document updated by " . $user->name . " at " . $request->local_time;
                } else {
                    $activity->activity_detail = "Document updated by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                }
                $activity->activity_icon = "user.png";
                $activity->local_time = $request->local_time;
                $activity->save();
                if ($request->filled('return_application_id')) {
                    return redirect()->route('view_application', $request->return_application_id)->with('document_updated', "Document updated successfully.");
                }
                return redirect()->route('client_documents')->with('document_updated', "Document updated successfully.");
            } else {
                $client = Clients::find($request->client_id);
                if ($client) {
                    $document = new Client_Docs();
                    $client = Clients::find($request->client_id);
                    $application = Applications::find($request->application_id);
                    $subscriber = User::find($client->subscriber_id);
                    $document->client_id = $request['client_id'];
                    $document->application_id = $application->application_id;
                    $document->user_id = $subscriber->id;
                    $document->doc_name = $request['doc_name'];
                    $document->doc_type = $request['doc_type'];
                    $document->doc_folder = $docFolder;
                    $document->doc_folders = $docFolders;
                    if ($request->hasFile('doc_file')) {
                        $file = $request->file('doc_file');
                        $filename = \App\Support\DocumentFileName::storageName($request->doc_name, $file->getClientOriginalName());
                        $file->move('web_assets/users/client' . $document->client_id . '/docs/', $filename);
                        $document->doc_file = $filename;
                    }
                    $document->save();
                    $activity = new Activities();
                    $activity->subscriber_id = $subscriber->id;
                    $activity->user_id = $user->id;
                    $activity->user_name = $user->name;
                    $activity->activity_name = "Document Added";
                    if ($user->user_type == "Subscriber") {
                        $activity->activity_detail = "New Document added by " . $user->name . " at " . $request->local_time;
                    } else {
                        $activity->activity_detail = "New Document added by " . $user->name . "(" . $subscriber->name . ") at " . $request->local_time;
                    }
                    $activity->activity_icon = "user.png";
                    $activity->local_time = $request->local_time;
                    $activity->save();
                    if ($request->filled('return_application_id')) {
                        return redirect()->route('view_application', $request->return_application_id)->with('document_added', 'Document added successfully.');
                    }
                    return redirect()->route('client_documents')->with('document_added', 'Document added successfully.');
                } else {
                    return back();
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function delete_client_document(Request $request, $id = null)
    {
        if (empty($id)) {
            return back();
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $this->set_timezone();
        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        $document = Client_Docs::find($id);
        if (!$document) {
            return back();
        }

        if ($user->user_type !== 'admin') {
            $application_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Applications')->first();
            if (!$application_roles || $application_roles->delete_only != 1) {
                return back();
            }
            $subscriberId = $user->user_type === 'Subscriber' ? $user->id : $user->added_by;
            if ((int) $document->user_id !== (int) $subscriberId) {
                return back();
            }
        }

        if ($user->user_type == "Subscriber") {
            $subscriber = $user;
        } else {
            $subscriber = User::find($user->added_by);
        }

        $docName = $document->doc_name;
        $document->delete();

        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "Document Deleted";
        if ($user->user_type == "Subscriber") {
            $activity->activity_detail = "Document " . $docName . " deleted by " . $user->name . " at " . $request->query('local_time', date('d M, Y H:i:s'));
        } else {
            $activity->activity_detail = "Document " . $docName . " deleted by " . $user->name . "(" . $subscriber->name . ") at " . $request->query('local_time', date('d M, Y H:i:s'));
        }
        $activity->activity_icon = "user.png";
        $activity->save();

        if ($request->filled('return_application_id')) {
            return redirect()->route('view_application', $request->return_application_id)->with('document_deleted', 'Document deleted successfully.');
        }

        return redirect()->route('client_documents')->with('document_deleted', 'Document deleted successfully.');
    }

    public function my_settings()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (membership_access_blocked($user)) {
            return redirect()->route('user_membership')->with("price_plan_expiry", "Please renew or upgrade your subscription plan.");
        }

        try {
            return view('web.my_settings', app(MySettingsPageService::class)->buildViewData($user));
        } catch (\Throwable $e) {
            Log::error('my_settings failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->view('errors.generic', [
                'statusCode' => 500,
                'message' => 'Something went wrong while loading Settings. Please try again later.',
            ], 500);
        }
    }

    public function update_my_currency(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string|max:20',
            'timezone' => 'required|timezone',
        ]);
        $user = Auth::user();
        if ($user) {
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }
            $all_users = User::where('added_by', '=', $subscriber->id)->get();
            foreach ($all_users as $one) {
                $one->currency = $validated['currency'];
                $one->save();
            }
            $user->timezone = $validated['timezone'];
            $user->currency = $validated['currency'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully.',
                'currency' => $user->currency,
                'timezone' => $user->timezone
            ]);
        } else {
            return redirect()->route('admin');
        }
    }

    public function user_role()
    {
        $user = Auth::user();
        if ($user->user_type == "Subscriber") {
            $accessRightsService = app(UserAccessRightsService::class);
            $siteUsers = User::where('added_by', '=', $user->id)->orderBy('name')->get();
            if (!\App\Support\ModuleAvailability::hasStaffUsers($user)) {
                return redirect()->route('users')->with('no_user', 'No user found..');
            }

            $roles = $siteUsers->map(function ($staff) use ($accessRightsService) {
                $userRoles = UserRoles::where('user_id', '=', $staff->id)->get();
                $accessType = $accessRightsService->resolveAccessTypeForUser($staff, $userRoles);
                $updatedAt = $userRoles->max('updated_at');

                return [
                    'user_id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'designation' => $staff->designation,
                    'access_right' => $accessRightsService->labelForAccessType($accessType),
                    'updated_at' => $updatedAt,
                ];
            });
            $page = "user_role";
            return view('web.user_role', compact('user', 'page', 'roles'));
        } else {
            return back();
        }
    }

    public function add_user_role($id = null)
    {
        $user = Auth::user();
        if ($user->user_type == "Subscriber") {
            $accessRightsService = app(UserAccessRightsService::class);
            $accessPresets = $accessRightsService->presetOptions();
            $page = "user_role";
            $siteusers = User::where('added_by', '=', $user->id)->orderBy('name')->get();

            if ($siteusers->isEmpty()) {
                return back()->with('no_user', 'No user found..');
            }

            $staff = null;
            $roles = collect();

            if ($id != null) {
                $staff = User::find($id);
                if (!$staff || (int) $staff->added_by !== (int) $user->id) {
                    return redirect()->route('add_user_role')->withErrors(['user_id' => 'User not found or not assigned to your account.']);
                }
                $roles = UserRoles::where('user_id', '=', $id)->get();
            }

            $detectedAccessType = $staff
                ? $accessRightsService->resolveAccessTypeForUser($staff, $roles)
                : 'full_access';
            $matrixRoles = $accessRightsService->buildMatrixFromAccessType($detectedAccessType, $roles);
            $presetPermissionsForJs = $accessRightsService->presetPermissionsForJs();

            return view('web.add_user_role', compact(
                'user',
                'page',
                'roles',
                'staff',
                'siteusers',
                'accessPresets',
                'detectedAccessType',
                'matrixRoles',
                'presetPermissionsForJs'
            ));
        } else {
            return back();
        }
    }

    public function user_role_post(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->user_type !== 'Subscriber') {
            return redirect()->route('login');
        }

        $request->validate([
            'user_id' => 'required|integer',
            'access_type' => 'required|string|in:full_access,director_manager,counsellor_advisor,sales_support,accountant,limited_access',
        ]);

        $staff = User::find($request->user_id);
        if (!$staff || (int) $staff->added_by !== (int) $user->id) {
            return redirect()->route('add_user_role')
                ->withErrors(['user_id' => 'Please select a valid user/advisor.'])
                ->withInput();
        }

        UserRoles::where('user_id', '=', $request->user_id)->delete();

        $accessRightsService = app(UserAccessRightsService::class);
        $accessRightsService->saveAccessRights($staff, $user->id, $request->access_type, $request);

        return redirect()->route('add_user_role', $request->user_id)
            ->with('role_added', 'Access rights updated successfully.');
    }

    public function delete_user_role($id = null)
    {
        $user = Auth::user();
        if ($id != null) {
            $role = UserRoles::find($id);
            $role->delete();
            return redirect()->route('user_role')->with('role_deleted', 'user role deleted');
        } else {
            return back();
        }
    }

    public function clientDatatable()
    {
        $user = Auth::user();
        $client_roles = null;
        if ($user->user_type != 'admin') {

            $client_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Clients')->first();
        }

        if (request()->ajax()) {

            $startDate = Carbon::parse($this->normalizeDateValue(request()->startdate) ?? request()->startdate)->startOfDay();
            $endDate = Carbon::parse($this->normalizeDateValue(request()->enddate) ?? request()->enddate)->endOfDay();

            if ($user->user_type == 'admin') {

                $clients = Clients::withCount('dependants')->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc')->get();
            } else {
                $clients = Clients::withCount('dependants')->where('subscriber_id', '=', $user->id)->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc')->get();
            }

            // dd($clients->toSql(),$clients->getBindings(),$startDate,$endDate);


            return DataTables::of($clients)
              ->addColumn('client_name',function ($row) use ($client_roles, $user) {
                 return $row->name.'('.$row->subscriber_id.')';
              })
                 ->addColumn('noa',function ($row) use ($client_roles, $user) {
                 return 1 + (int) ($row->dependants_count ?? 0);
              })
              ->editColumn('created_at', function ($row) {
                return date("d-m-Y H:i:s", strtotime($row->created_at));
            })
                ->addColumn('action', function ($row) use ($client_roles, $user) {


                    $html = '<a';
                    if ($user->user_type == 'admin' || ($client_roles->read_only == 1 || $client_roles->read_write_only == 1)) {
                        $html .= ' href="' . route('client_profile', $row->id) . '" ';
                    } else {
                        $html .= ' href = "#"';
                    }

                    $html .= 'style="text-decoration:none; background:none;border:none;padding:0px" > <i class="fa-solid fa-eye btn p-1 text-info" style="font-size:12px;"></i></a>';
                    // <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:12px;" ';

                    // if($client_roles->delete_only == 1)
                    // {
                    //     $html .= 'onclick="deleteclient(' . $row->id . ')" ';

                    // }
                    // $html .= '></i>';
                    return $html;








                    $html = '<a ';

                    if ($client_roles->read_only == 1 || $client_roles->read_write_only == 1) {
                        $html .= 'href="' . route('client_profile', $row->id) . '" ';
                    } else {
                        $html .= 'href="#" ';
                    }

                    $html .= 'style="text-decoration:none;"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:12px;"></i></a>';

                    $html .= '<i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:12px;" ';

                    if ($client_roles->delete_only == 1) {
                        $html .= 'onclick="deleteclient(' . $row->id . ')" ';
                    }

                    $html .= '></i>';

                    return $html;
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function Affiliates_Reg()
    {
        $countries = Countries::get();
        return view('web.affiliates_reg', compact('countries'));
    }
    public function Affiliates_store(Request $req)
    {

        if($req->has('terms') && $req->terms == 'admin'){
            $validated = $req->validate([
                'name' => 'required',
                'phone' => 'required|phone_intl|unique:affiliates',
                'email' => 'required|email|unique:affiliates,email',
                'type' => 'required',
                'country' => 'required',
                'city' => 'required',
                'password' => 'required',
            ]);
        }else{
            $validated = $req->validate([
                'name' => 'required',
                'phone' => 'required|phone_intl|unique:affiliates',
                'email' => 'required|email|unique:affiliates,email',
                'type' => 'required',
                'country' => 'required',
                'city' => 'required',
                'password' => 'required',
                'terms' => 'required|accepted',
                'g-recaptcha-response' => 'required|captcha'
                
            ]);
        }



        $validated['password'] = Hash::make($validated['password']);
        try {
            DB::beginTransaction();


            Affiliates::create($validated);

            $user =  User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'wallet' => 0,
                    'user_type' => 'Affiliate',
                    'password' => $validated['password'],
                    'referral' => $this->get_referral(),
                    'referral_code' => $req['referral'],
                    'terms_accepted_at' => now(),
                    'local_time' => $req['local_time']
                ]
            );
            // if($req['referral'] != null){
            //     $find_referral = User::where('referral','=',$req['referral'])->first();
            // }
            $activity = new Activities();
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "New Affiliate Added";
            $activity->activity_detail = "New Affiliate " . $user->name . " registered at " . $req['local_time'];
            $activity->activity_icon = "user.png";
            $activity->local_time = $req['local_time'];
            $activity->save();
            // if(isset($find_referral)){
            //     $wallet = $find_referral->wallet;
            //     $find_referral->wallet = $wallet + 10;
            //     $find_referral->save();

            //     $save_referral = new Referrals();
            //     $save_referral->referral_code = $req['referral'];
            //     $save_referral->userid = $user->id;
            //     $save_referral->user_name = $user->name;
            //     $save_referral->total_amount = 10;
            //     $save_referral->amount_added = 10;
            //     $save_referral->previous_balance = $wallet;
            //     $save_referral->wallet_balance = $wallet + 10;
            //     $save_referral->save();

            //     $use_referral = new Used_referrals();
            //     $use_referral->referral_code = $req['referral'];
            //     $use_referral->user_id = $user->id;
            //     $use_referral->commission_earnt = 10;
            //     $use_referral->save();

            //     $affiliate_commission = AffiliateCommissionEarnt::where('referral_code',$req['referral'])->first();
            //     if($affiliate_commission)
            //     {

            //         $affiliate_commission->total_earned += 10;
            //         $affiliate_commission->save();
            //     }
            //     else
            //     {

            //         $use_referral = new AffiliateCommissionEarnt();
            //         $use_referral->referral_code = $req['referral'];
            //         $use_referral->total_earned = 10;
            //         $use_referral->save();
            //     }
            // }

            DB::commit();
            if ($req->has('url_model')) {
                return redirect()->route('affiliates')->with('msg', 'Affiliate Added Successfully');
            }
            return redirect()->route('/')->with('success', 'Submitted Successfully');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('/')->with('msg', $th->getMessage());
        }
    }
    public function Affiliates_ceateLogin()
    {
        return view('web.affiliate_login');
    }
    public function Affiliates_storeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => 'required|captcha'
        ]);

        $loginDetails = $request->except(['_token', 'g-recaptcha-response', 'local_time']);
        $affiliates = Affiliates::where('email', $credentials['email'])->first();
        if(isset($affiliates)){
            if ($affiliates->status == 1) {
                if (Auth::guard('affiliates')->attempt($loginDetails)) {
                    $request->session()->regenerate();
                    return redirect()->route(app(\App\Services\RoleModuleAccessService::class)->affiliateHomeRoute());
                }
            } else {
                return back()->withErrors([
                    'email' => 'Your account is still not activated.',
                ]);
            }
        }else{
            return back()->withErrors([
                'email' => 'Login Attempt by a non-affiliate user',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function subscribers_affiliate()
    {
        $affiliateUser = Auth::guard('affiliates')->user();
        if (!$affiliateUser) {
            return redirect()->route('affiliate.createLogin');
        }

        $user = User::where('email', $affiliateUser->email)->first();
        if (!$user) {
            return redirect()->route('affiliate.createLogin');
        }

        $user['type_user'] = 'affiliate';
        $page = 'subscribers';
        $subscribers = User::where('user_type', 'Subscriber')
            ->where('referral_code', $user->referral)
            ->orderByDesc('created_at')
            ->get();

        return view('affiliate.subscribers', compact('user', 'page', 'subscribers', 'affiliateUser'));
    }

    public function commissions_affiliate()
    {
        $affiliateUser = Auth::guard('affiliates')->user();
        if (!$affiliateUser) {
            return redirect()->route('affiliate.createLogin');
        }

        $user = User::where('email', $affiliateUser->email)->first();
        if (!$user) {
            return redirect()->route('affiliate.createLogin');
        }

        $user['type_user'] = 'affiliate';
        $page = 'commissions';

        $commissions = Referrals::with('user')
            ->where('referral_code', $user->referral)
            ->whereIn('type', ['Referral Commission', \App\Services\RenewalCommissionService::TYPE])
            ->orderByDesc('created_at')
            ->get();

        $commissionSummary = AffiliateCommissionEarnt::where('referral_code', $user->referral)->first();
        $totalEarned = $commissionSummary
            ? (float) $commissionSummary->total_earned
            : (float) $commissions->sum('amount_added');
        $paidAmount = $commissionSummary ? (float) $commissionSummary->paid_amount : 0;
        $pendingAmount = $commissionSummary
            ? (float) $commissionSummary->pending_amount
            : max($totalEarned - $paidAmount, 0);

        return view('affiliate.commissions', compact(
            'user',
            'page',
            'commissions',
            'affiliateUser',
            'totalEarned',
            'paidAmount',
            'pendingAmount'
        ));
    }

    public function dashboard_affiliate()
    {
        return redirect()->route(app(\App\Services\RoleModuleAccessService::class)->affiliateHomeRoute());
    }

    public function Affiliates_forget_create()
    {
        return view('web.affiliate_forget');
    }
    public function get_referral()
    {
        $ch = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ref = "";
        for ($i = 0; $i < 8; $i++) {
            $ref = $ref . $ch[rand(0, strlen($ch) - 1)];
        }
        $referal = User::where('referral', '=', $ref)->first();
        if ($referal) {
            $this->get_referral();
        } else {
            return $ref;
        }
    }
    public function save_dashboard_settings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'headers' => 'nullable|array',
            'headers.*' => 'nullable|string|max:50',
            'charts' => 'nullable|array',
            'charts.*.module' => 'nullable|string|max:50',
            'charts.*.filter' => 'nullable|string|max:50',
            'charts.*.duration' => 'nullable|string|max:50',
            'charts.*.chart_type' => 'nullable|string|max:20',
            'chart_count' => 'nullable|integer|in:4',
            'reset_defaults' => 'nullable|boolean',
            'reset_headers' => 'nullable|boolean',
            'reset_charts' => 'nullable|boolean',
        ]);

        $ccService = app(CountryCategorySettingsService::class);
        $subscriber = $ccService->resolveSubscriber($user);
        $dashboardService = app(DashboardPreferenceService::class);

        if (!empty($validated['reset_headers'])) {
            $dashboardService->saveSettings(
                $subscriber,
                $dashboardService->defaultHeaders(),
                $dashboardService->resolveCharts($subscriber),
                $dashboardService->resolveChartCount($subscriber)
            );

            return response()->json([
                'success' => true,
                'message' => 'Header preferences reset to defaults.',
                'headers' => $dashboardService->resolveHeaders($subscriber),
                'charts' => $dashboardService->resolveCharts($subscriber),
                'chart_count' => $dashboardService->resolveChartCount($subscriber),
            ]);
        }

        if (!empty($validated['reset_charts'])) {
            $chartCount = DashboardPreferenceService::DEFAULT_CHART_COUNT;
            $dashboardService->saveSettings(
                $subscriber,
                $dashboardService->resolveHeaders($subscriber),
                $dashboardService->defaultCharts($chartCount),
                $chartCount
            );

            return response()->json([
                'success' => true,
                'message' => 'Chart preferences reset to defaults.',
                'headers' => $dashboardService->resolveHeaders($subscriber),
                'charts' => $dashboardService->resolveCharts($subscriber),
                'chart_count' => $dashboardService->resolveChartCount($subscriber),
            ]);
        }

        if (!empty($validated['reset_defaults'])) {
            $chartCount = DashboardPreferenceService::DEFAULT_CHART_COUNT;
            $dashboardService->saveSettings(
                $subscriber,
                $dashboardService->defaultHeaders(),
                $dashboardService->defaultCharts($chartCount),
                $chartCount
            );

            return response()->json([
                'success' => true,
                'message' => 'Dashboard preferences reset to defaults.',
                'headers' => $dashboardService->resolveHeaders($subscriber),
                'charts' => $dashboardService->resolveCharts($subscriber),
                'chart_count' => $dashboardService->resolveChartCount($subscriber),
            ]);
        }

        $dashboardService->saveSettings(
            $subscriber,
            $validated['headers'] ?? [],
            $validated['charts'] ?? [],
            $validated['chart_count'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard preferences saved.',
            'headers' => $dashboardService->resolveHeaders($subscriber),
            'charts' => $dashboardService->resolveCharts($subscriber),
            'chart_count' => $dashboardService->resolveChartCount($subscriber),
        ]);
    }

    public function save_enquiry_form_settings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            $validated = $request->validate([
                'sections' => 'nullable|array',
                'sections.*' => 'nullable|in:0,1,true,false,on,off,yes,no',
                'reset_defaults' => 'nullable|in:0,1,true,false,on,off,yes,no',
            ]);

            $ccService = app(CountryCategorySettingsService::class);
            $subscriber = $ccService->resolveSubscriber($user);
            $enquiryFormService = app(EnquiryFormSettingsService::class);

            if (!empty($validated['reset_defaults']) && filter_var($validated['reset_defaults'], FILTER_VALIDATE_BOOLEAN)) {
                $enquiryFormService->resetToDefaults($subscriber);

                return response()->json([
                    'success' => true,
                    'message' => 'Enquiry form settings reset to defaults.',
                    'sections' => $enquiryFormService->resolveSections($subscriber),
                ]);
            }

            $enquiryFormService->saveSettings($subscriber, $validated['sections'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry form settings saved.',
                'sections' => $enquiryFormService->resolveSections($subscriber),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            throw $validationException;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to save enquiry form settings.',
            ], 500);
        }
    }

    public function save_cc_settings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            $validated = $request->validate([
                'countries' => 'nullable|array',
                'countries.*' => 'string|max:255',
                'visa_categories' => 'nullable|array',
                'visa_categories.*' => 'string|max:255',
                'document_lists' => 'nullable|array',
                'document_lists.*.country' => 'required_with:document_lists|string|max:255',
                'document_lists.*.visa_category' => 'required_with:document_lists|string|max:255',
                'document_lists.*.documents' => 'nullable|array',
                'document_lists.*.documents.*' => 'string|max:255',
                'document_lists.*.sections' => 'nullable|array',
                'document_lists.*.sections.*.title' => 'nullable|string|max:255',
                'document_lists.*.sections.*.documents' => 'nullable|array',
                'document_lists.*.sections.*.documents.*' => 'string|max:255',
                'reset_defaults' => 'nullable|boolean',
            ]);

            $ccService = app(CountryCategorySettingsService::class);
            $subscriber = $ccService->resolveSubscriber($user);
            $previousCountries = $ccService->resolveCountryNames($subscriber)->values()->all();
            $previousCategories = $ccService->resolveVisaCategoryNames($subscriber)->values()->all();

            if (!empty($validated['reset_defaults'])) {
                $ccService->resetToDefaults($subscriber);

                try {
                    app(\App\Services\NotificationService::class)->notifyConsultancyUsers(
                        $subscriber,
                        'new_countries',
                        'Countries & categories reset to defaults',
                        'Your consultancy country and visa category settings were reset to system defaults.',
                        route('my_settings') . '#cc-settings'
                    );
                } catch (\Throwable $notificationError) {
                    report($notificationError);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Countries & Categories reset to sub-category defaults.',
                    'countries' => $ccService->getDefaultCountryNames($subscriber)->values(),
                    'visa_categories' => $ccService->getDefaultVisaCategoryNames($subscriber)->values(),
                    'document_lists' => [],
                    'service_countries' => $ccService->resolveServiceCountryOptions($subscriber)->values(),
                    'service_names' => $ccService->resolveServiceNameOptions($subscriber)->values(),
                    'service_cc_preferences' => $ccService->resolveSavedServicePreferences($subscriber),
                ]);
            }

            $countries = $validated['countries'] ?? [];
            $visaCategories = $validated['visa_categories'] ?? [];
            $documentLists = array_key_exists('document_lists', $validated) ? ($validated['document_lists'] ?? []) : null;

            if (count($countries) === 0 || count($visaCategories) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one country and one visa category.',
                ], 422);
            }

            $ccService->saveSettings($subscriber, $countries, $visaCategories, $documentLists);

            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $addedCountries = array_values(array_diff($countries, $previousCountries));
                $addedCategories = array_values(array_diff($visaCategories, $previousCategories));
                $removedCountries = array_values(array_diff($previousCountries, $countries));
                $removedCategories = array_values(array_diff($previousCategories, $visaCategories));

                if (!empty($addedCountries)) {
                    $notificationService->notifyConsultancyUsers(
                        $subscriber,
                        'new_countries',
                        'New countries added',
                        'Countries added: ' . implode(', ', $addedCountries),
                        route('my_settings') . '#cc-settings'
                    );
                }

                if (!empty($addedCategories)) {
                    $notificationService->notifyConsultancyUsers(
                        $subscriber,
                        'new_categories',
                        'New visa categories added',
                        'Categories added: ' . implode(', ', $addedCategories),
                        route('my_settings') . '#cc-settings'
                    );
                }

                if (!empty($removedCountries) || !empty($removedCategories)) {
                    $notificationService->notifyConsultancyUsers(
                        $subscriber,
                        'visa_rules',
                        'Country or category settings updated',
                        'Your consultancy country/category configuration has been updated.',
                        route('my_settings') . '#cc-settings'
                    );
                }
            } catch (\Throwable $notificationError) {
                report($notificationError);
            }

            return response()->json([
                'success' => true,
                'message' => 'Countries & Categories saved successfully.',
                'countries' => collect($countries)->values(),
                'visa_categories' => collect($visaCategories)->values(),
                'document_lists' => $ccService->getDocumentLists($subscriber),
                'service_countries' => $ccService->resolveServiceCountryOptions($subscriber)->values(),
                'service_names' => $ccService->resolveServiceNameOptions($subscriber)->values(),
                'service_cc_preferences' => $ccService->resolveSavedServicePreferences($subscriber),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            throw $validationException;
        } catch (\InvalidArgumentException $argumentException) {
            return response()->json([
                'success' => false,
                'message' => $argumentException->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to save Countries & Categories settings.',
            ], 500);
        }
    }

    public function save_cc_document_lists(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            $validated = $request->validate([
                'document_lists' => 'present|array',
                'document_lists.*.country' => 'required|string|max:255',
                'document_lists.*.visa_category' => 'required|string|max:255',
                'document_lists.*.documents' => 'nullable|array',
                'document_lists.*.documents.*' => 'string|max:255',
                'document_lists.*.sections' => 'nullable|array',
                'document_lists.*.sections.*.title' => 'nullable|string|max:255',
                'document_lists.*.sections.*.documents' => 'nullable|array',
                'document_lists.*.sections.*.documents.*' => 'string|max:255',
            ]);

            $ccService = app(CountryCategorySettingsService::class);
            $subscriber = $ccService->resolveSubscriber($user);
            $ccService->saveDocumentLists($subscriber, $validated['document_lists']);

            return response()->json([
                'success' => true,
                'message' => 'Document lists saved successfully.',
                'document_lists' => $ccService->getDocumentLists($subscriber),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            throw $validationException;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to save document lists.',
            ], 500);
        }
    }

    public function get_service_fee(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['fee' => null], 401);
        }

        $applicationType = trim((string) $request->input('application_type', ''));
        $visaCountry = trim((string) $request->input('visa_country', $request->input('country', '')));
        $ccService = app(CountryCategorySettingsService::class);

        $subscriberId = (int) $request->input('subscriber_id', 0);
        if ($subscriberId > 0 && strtolower((string) $user->user_type) === 'admin') {
            $subscriber = User::where('id', $subscriberId)->where('user_type', 'Subscriber')->first();
            if (!$subscriber) {
                return response()->json(['fee' => null, 'message' => 'Subscriber not found.'], 404);
            }
        } else {
            $subscriber = $ccService->resolveSubscriber($user);
        }

        $serviceName = $ccService->formatApplicationServiceName($visaCountry, $applicationType);
        $fee = $ccService->resolveServiceFee($subscriber, $applicationType, $visaCountry);

        return response()->json([
            'fee' => $fee,
            'service_name' => $serviceName !== '' ? $serviceName : null,
        ]);
    }

    public function add_service(Request $request){
        try {
            // Status-only update from Services data-table
            if (!empty($request->input('id')) && $request->has('status') && !$request->filled('service_name')) {
                $service = Services::find($request->input('id'));
                if (!$service) {
                    return response()->json(['message' => 'Service not found.'], 404);
                }

                $wasActive = (bool) $service->status;
                $newStatus = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
                $service->status = $newStatus;
                $service->save();

                if ($wasActive && !$newStatus) {
                    $subscriber = User::find($service->subscriber_id);
                    if ($subscriber) {
                        try {
                            app(\App\Services\OperationalNotificationService::class)->notifyServiceDeactivated(
                                $subscriber,
                                $service->service_name ?: 'Service'
                            );
                        } catch (\Throwable $notificationError) {
                            report($notificationError);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Service status updated successfully.',
                ]);
            }

            $validated = $request->validate([
                'country' => 'required|string|max:100',
                'service_name' => 'required|string|max:255',
                'fees' => 'required|numeric|min:0|max:9999999999.99',
            ], [
                'country.required' => 'Country is required.',
                'service_name.required' => 'Service name is required.',
                'fees.required' => 'Fees are required.',
                'fees.numeric' => 'Fees must be a valid number.',
                'fees.min' => 'Fees must be 0 or greater.',
                'fees.max' => 'Fees cannot exceed 9,999,999,999.99.',
            ]);

            $this->ensureServicesCountryColumn();

            $country = Services::normalizeCountry($validated['country']);
            $serviceName = Services::normalizeName($validated['service_name']);

            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            $ccService = app(CountryCategorySettingsService::class);
            $existingService = null;
            if (!empty($request->input('id'))) {
                $existingService = Services::find($request->input('id'));
                if (!$existingService) {
                    return response()->json(['message' => 'Service not found.'], 404);
                }
                $subscriber = User::find($existingService->subscriber_id);
            } else {
                $subscriber = $ccService->resolveSubscriber($user);
            }

            if (!$subscriber) {
                return response()->json(['message' => 'Subscriber not found.'], 404);
            }

            $allowedCountries = collect([Services::COUNTRY_NA])
                ->merge($ccService->resolveServiceCountryOptions($subscriber, [$country]));
            $allowedServiceNames = $ccService->resolveServiceNameOptions($subscriber, [$serviceName]);

            if (!$allowedCountries->contains(fn ($value) => strcasecmp((string) $value, $country) === 0)) {
                return response()->json([
                    'message' => 'Please select a country from your C & C settings list.',
                ], 422);
            }

            if (!$allowedServiceNames->contains(fn ($value) => strcasecmp((string) $value, $serviceName) === 0)) {
                return response()->json([
                    'message' => 'Please select a service name from the available list.',
                ], 422);
            }

            if (!empty($request->input('id'))) {
                $service = $existingService;
                $subscriberId = (int) $service->subscriber_id;
                if (Services::duplicateExists($subscriberId, $country, $serviceName, (int) $service->id)) {
                    return response()->json([
                        'message' => 'A service with this country and service name already exists.',
                    ], 422);
                }

                $oldFees = $service->fees;
                $oldName = $service->service_name;
                $service->country = $country;
                $service->service_name = $serviceName;
                $service->fees = $validated['fees'];
                $this->persistServiceRecord($service);
                $message = 'Service updated successfully.';

                $subscriber = User::find($service->subscriber_id);
                if ($subscriber && (float) $oldFees !== (float) $service->fees) {
                    try {
                        app(\App\Services\OperationalNotificationService::class)->notifyServiceFeeUpdated(
                            $subscriber,
                            $service->service_name ?: $oldName,
                            $service->fees
                        );
                    } catch (\Throwable $notificationError) {
                        report($notificationError);
                    }
                }
            } else {
                $subscriberId = empty($user->added_by) ? (int) $user->id : (int) $user->added_by;
                if (Services::duplicateExists($subscriberId, $country, $serviceName)) {
                    return response()->json([
                        'message' => 'A service with this country and service name already exists.',
                    ], 422);
                }

                $service = new Services();
                $service->country = $country;
                $service->service_name = $serviceName;
                $service->fees = $validated['fees'];
                $service->subscriber_id = empty($user->added_by) ? $user->id : $user->added_by;
                $service->user_id = $user->id;
                $service->status = true;
                $this->persistServiceRecord($service);
                $message = 'Service created successfully.';

                $subscriber = User::find($service->subscriber_id);
                if ($subscriber) {
                    try {
                        app(\App\Services\NotificationService::class)->notifyConsultancyUsers(
                            $subscriber,
                            'new_products',
                            'New service added: ' . $service->service_name,
                            'A new product/service "' . $service->service_name . '" has been added to your consultancy.',
                            route('my_settings') . '#service'
                        );
                    } catch (\Throwable $notificationError) {
                        report($notificationError);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $message = 'Unable to save service. Please try again.';
            if ($e instanceof \Illuminate\Database\QueryException) {
                $sqlMessage = strtolower($e->getMessage());
                if (str_contains($sqlMessage, 'fees') || str_contains($sqlMessage, '1264') || str_contains($sqlMessage, 'truncated')) {
                    $message = 'Unable to save service. The fee amount may be too large for the current database column — run the latest migrations.';
                } elseif (str_contains($sqlMessage, 'country') || str_contains($sqlMessage, 'unknown column')) {
                    $message = 'Unable to save service. Database schema is outdated — run the latest migrations.';
                } else {
                    $message = 'Unable to save service due to a database error. Please try again.';
                }
            }

            return response()->json(['message' => $message], 500);
        }
    }

    /**
     * Ensure services.country exists for older databases that predate the country column.
     */
    private function ensureServicesCountryColumn(): void
    {
        if (!Schema::hasTable('services') || Schema::hasColumn('services', 'country')) {
            return;
        }

        Schema::table('services', function ($table) {
            $table->string('country', 100)->default('NA')->after('subscriber_id');
        });
    }

    /**
     * Widen services.fees when legacy DECIMAL(8,2) rejects large amounts.
     */
    private function ensureServicesFeesColumnWidth(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'fees')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE `services` MODIFY `fees` DECIMAL(12, 2) NOT NULL DEFAULT 0');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function persistServiceRecord(Services $service): void
    {
        try {
            $service->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $sqlMessage = strtolower($e->getMessage());
            $looksLikeFeesOverflow = str_contains($sqlMessage, 'fees')
                || str_contains($sqlMessage, '1264')
                || str_contains($sqlMessage, 'out of range')
                || str_contains($sqlMessage, 'truncated');

            if (!$looksLikeFeesOverflow) {
                throw $e;
            }

            $this->ensureServicesFeesColumnWidth();
            $service->save();
        }
    }



    public function get_subscriber_service(){

           $user = auth()->user();
        if ($user->user_type == "Subscriber") {
            $services = Services::where('subscriber_id',$user->id)->orderBy('id','desc')->get();
        } else {
            $services = Services::where('user_id',$user->id)->orderBy('id','desc')->get();
        }
        return DataTables::of($services)
        ->addIndexColumn()
        ->editColumn('country', function ($row) {
            return e($row->displayCountry());
        })
        ->editColumn('subscriber', function ($row) {
            return $row->subscriber ? $row->subscriber->name . '(' . $row->subscriber_id . ')' : 'N/A';
        })
        ->editColumn('user', function ($row) {
            return $row->user ? $row->user->name . '(' . $row->user_id . ')' : 'N/A';
        })
        ->editColumn('status', function ($row) {
            $isActive = (bool) $row->status;
            $nextStatus = $isActive ? 0 : 1;
            $label = $isActive ? 'Active' : 'Deactivated';
            $stateClass = $isActive ? 'is-active' : 'is-deactivated';
            $title = $isActive ? 'Active — click to deactivate' : 'Deactivated — click to activate';

            return '<button type="button" class="service-status-switch ' . $stateClass . '" '
                . 'data-id="' . $row->id . '" data-status="' . $nextStatus . '" title="' . e($title) . '" '
                . 'aria-label="' . e($title) . '" aria-pressed="' . ($isActive ? 'true' : 'false') . '">'
                . '<span class="service-status-track" aria-hidden="true"><span class="service-status-thumb"></span></span>'
                . '<span class="visually-hidden">' . e($label) . '</span>'
                . '</button>';
        })
        ->addColumn('action', function ($row) {
            // $deleteUrl = route('services_delete', $row->id); // Define delete route

            return '
                <a href="javascript:void(0)"
                   class="editService"
                   data-id="' . $row->id . '"
                   data-country="' . e($row->displayCountry()) . '"
                   data-name="' . e($row->service_name) . '"
                   data-fee="' . $row->fees . '"
                   style="text-decoration:none; background:none; border:none">
                    <i class="fa-solid fa-edit btn p-1 text-primary" style="font-size:14px;"></i>
                </a>
               <a href="javascript:void(0)"
                    onclick="deleteService('.$row->id .')"
                    style="text-decoration:none; background:none; border:none">
                        <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;"></i>
                    </a>';
        })
        ->rawColumns(['status', 'action'])
        ->make(true);

    }
    public function services_delete($id){
        $service = Services::findOrFail($id);
        $subscriber = User::find($service->subscriber_id);
        $serviceName = $service->service_name;
        $service->delete();

        if ($subscriber) {
            app(\App\Services\OperationalNotificationService::class)
                ->notifyServiceDiscontinued($subscriber, $serviceName);
        }

     return response()->json(['message' => 'Service deleted successfully.']);

    }
    public function storeFeedback(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string',
            'rating' => 'required|string',
        ]);
    $user = auth()->user();
        Feedbacks::create([
            'feedback' => $request->feedback,
            'rating' => $request->rating,
            'subscriber_id' => empty($user->added_by) ? $user->id : $user->added_by,
            'user_id' => $user->id
        ]);


        return response()->json(['message' => 'Thank you! Your feedback was received.']);
    }

    public function showFeedbackPopup()
    {
        $user = auth()->user();
        $feedbackPopupService = app(\App\Services\FeedbackPopupService::class);

        if (!$user || !$feedbackPopupService->shouldShowPopup($user)) {
            return response()->json(['show_popup' => false]);
        }

        return response()->json([
            'show_popup' => true,
            'id' => $user->id,
        ]);
    }

    /*Newly added code by Meenakshi Nanta*/
    public function enquiries()
    {
        $user = $this->check_login();

        if (membership_access_blocked($user)) {
            return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
        }

        $this->set_timezone();

        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        if ($user->user_type != "admin" && $user->user_type != "Subscriber") {
            if (!$subscriber || membership_access_blocked_for_subscriber($subscriber)) {
                return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
            }
        } elseif ($user->user_type == "Subscriber" && membership_access_blocked($user)) {
            return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
        }

        $leadService = app(LeadEnquiryService::class);

        $enquiries = VisaEnquiry::with(['workedByUser'])
                        ->withCount(['children as children_applying_count' => function ($query) {
                            $query->where('apply_together', 1);
                        }, 'followUpLogs as follow_up_logs_count'])
                        ->where('subscriber_id', $subscriber->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $staffMembers = User::where('added_by', $subscriber->id)
            ->where('user_type', 'User')
            ->orderBy('name')
            ->get(['id', 'name']);

        $page = "enquiries";

        return view('web.enquiries', compact(
            'user',
            'enquiries',
            'page',
            'subscriber',
            'staffMembers',
            'leadService'
        ));
    }

    public function updateLeadFollowUp(Request $request, $id)
    {
        $user = $this->check_login();
        $this->set_timezone();

        $request->validate([
            'lead_source' => 'nullable|string|max:50',
            'lead_status' => 'nullable|string|max:50',
            'lead_worked_by_user_id' => 'nullable|integer|exists:users,id',
            'follow_up_remarks' => 'nullable|string|max:1000',
        ]);

        $enquiry = VisaEnquiry::find($id);

        if (!$enquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found.',
            ], 404);
        }

        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        if ((int) $enquiry->subscriber_id !== (int) $subscriber->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update this enquiry.',
            ], 403);
        }

        $leadService = app(LeadEnquiryService::class);
        $enquiry = $leadService->applyLeadFollowUpUpdate($enquiry, $request->only([
            'lead_source',
            'lead_status',
            'lead_worked_by_user_id',
            'follow_up_remarks',
        ]), $user);

        return response()->json([
            'success' => true,
            'message' => 'Lead follow-up updated successfully.',
            'data' => [
                'lead_source' => $enquiry->lead_source,
                'lead_status' => $enquiry->lead_status,
                'lead_worked_by_user_id' => $enquiry->lead_worked_by_user_id,
                'lead_worked_by_name' => optional($enquiry->workedByUser)->name,
                'lead_worked_at' => optional($enquiry->lead_worked_at)->format('d-m-Y H:i:s'),
                'follow_up_logs_count' => $enquiry->followUpLogs()->count(),
            ],
        ]);
    }

    public function leadFollowUpHistoryData(Request $request, $id)
    {
        $user = $this->check_login();
        $this->set_timezone();

        $enquiry = VisaEnquiry::find($id);

        if (!$enquiry) {
            return response()->json([
                'message' => 'Enquiry not found.',
            ], 404);
        }

        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        if ((int) $enquiry->subscriber_id !== (int) $subscriber->id) {
            return response()->json([
                'message' => 'You are not allowed to view follow-ups for this enquiry.',
            ], 403);
        }

        $rows = app(LeadEnquiryService::class)->followUpHistoryRows($enquiry);

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => count($rows),
            'recordsFiltered' => count($rows),
            'data' => $rows,
            'total' => count($rows),
        ]);
    }

    public function convertEnquiryClient(Request $request){
        $user = Auth::user();
        $this->set_timezone();

        try {

            DB::beginTransaction();

            $enquiry = VisaEnquiry::with('children')->find($request->enquiry_id);

            if (!$enquiry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enquiry not found.'
                ]);
            }

            if ((int) $enquiry->status === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This enquiry has already been converted to a client.'
                ]);
            }

            /* Determine subscriber */
            if ($user->user_type == "Subscriber") {
                $subscriber = $user;
            } else {
                $subscriber = User::find($user->added_by);
            }

            /* Create Client */
            $existingClient = null;
            if (!empty($enquiry->email) || !empty($enquiry->contact_no)) {
                $existingClient = Clients::where('subscriber_id', $subscriber->id)
                    ->where(function ($query) use ($enquiry) {
                        if (!empty($enquiry->email)) {
                            $query->orWhere('email', $enquiry->email);
                        }

                        if (!empty($enquiry->contact_no)) {
                            $query->orWhere('phone', $enquiry->contact_no);
                        }
                    })
                    ->first();
            }

            if ($existingClient) {
                return response()->json([
                    'success' => false,
                    'message' => 'A client with the same email or contact number already exists.'
                ]);
            }

            $offerBenefitService = app(OfferBenefitService::class);
            $membership_plan = Membership::where('plan_name', '=', $subscriber->membership)->first();
            if ($membership_plan && strcasecmp((string) $membership_plan->client_limit, 'Unlimited') !== 0) {
                if (!$offerBenefitService->canAddClient($subscriber)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Client limit reached. Upgrade membership to add more clients.',
                    ]);
                }
            }

            $client = new Clients();
            $client->subscriber_id = $subscriber->id;
            $client->user_id = $user->id;

            $client->name = $enquiry->full_name;
            $client->phone = $enquiry->contact_no;
            $client->email = $enquiry->email;

            $client->alternate_no = null;

            $client->nationality = $enquiry->nationality ?? null;
            $client->passport_no = $enquiry->passport_no ?? null;
            $client->dob = $enquiry->dob ?? null;

            $client->address = $enquiry->address;
            $client->country = $enquiry->country ?? null;
            $client->state = $enquiry->state ?? null;
            $client->city = $enquiry->city ?? $enquiry->place ?? null;
            $client->pincode = $enquiry->postcode ?? $enquiry->pincode ?? null;

            $client->save();

            if (!empty($enquiry->spouse_name) && (int) ($enquiry->spouse_apply_together ?? 0) === 1) {
                $spouseDependantData = [
                    'client_id' => $client->id,
                    'subscriber_id' => $subscriber->id,
                    'name' => $enquiry->spouse_name,
                    'dob' => $enquiry->spouse_dob ?? null,
                    'relation' => 'Spouse',
                ];

                if (Schema::hasColumn('dependants', 'age')) {
                    $spouseDependantData['age'] = $enquiry->spouse_age ?? null;
                }

                if (Schema::hasColumn('dependants', 'qualification')) {
                    $spouseDependantData['qualification'] = $enquiry->spouse_qualification ?? null;
                }

                if (Schema::hasColumn('dependants', 'work_experience_years')) {
                    $spouseDependantData['work_experience_years'] = $enquiry->spouse_work_experience_years ?? null;
                }

                Dependants::create($spouseDependantData);
            }

            foreach ($enquiry->children as $child) {
                if ((int) ($child->apply_together ?? 0) !== 1) {
                    continue;
                }
                if (empty($child->child_name)) {
                    continue;
                }

                Dependants::create([
                    'client_id' => $client->id,
                    'subscriber_id' => $subscriber->id,
                    'name' => $child->child_name,
                    'dob' => $child->child_dob ?? null,
                    'relation' => 'Child',
                    'gender' => $child->child_gender ?? null,
                ]);
            }

            /* Update enquiry status */
            $enquiry->status = 1;
            app(LeadEnquiryService::class)->syncLeadStatusOnConversion($enquiry, $user, (int) $client->id);

            /* Activity Log */
            $activity = new Activities();
            $activity->subscriber_id = $subscriber->id;
            $activity->user_id = $user->id;
            $activity->user_name = $user->name;
            $activity->activity_name = "Enquiry Converted To Client";

            if ($user->user_type == "Subscriber") {
                $activity->activity_detail = "Enquiry ".$enquiry->full_name." converted to client by ".$user->name;
            } else {
                $activity->activity_detail = "Enquiry ".$enquiry->full_name." converted to client by ".$user->name." (".$subscriber->name.")";
            }

            $activity->activity_icon = "user.png";
            $activity->local_time = now();
            $activity->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enquiry converted to client successfully.'
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function deleteEnquiry($id)
    {
        $enquiry = VisaEnquiry::find($id);

        if(!$enquiry){
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found.'
            ]);
        }

        $enquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted successfully.'
        ]);
    }

    public function viewEnquiry($id)
    {
        $enquiry = VisaEnquiry::with(['residencyHistory','travelHistory','refusalHistory','workExperience','children','fundingSources','workedByUser'])->find($id);

        if(!$enquiry){
            return redirect()->back()->with('error', 'Enquiry not found.');
        }
        $user = $this->check_login();
        $page = "visa-enquiries";
        return view('web.view_enquiries', compact('user','enquiry','page'));
    }

    public function editEnquiry($id)
    {
        $user = $this->check_login();

        $enquiry = VisaEnquiry::with(['residencyHistory','travelHistory','refusalHistory','workExperience','children','fundingSources'])->find($id);

        if (!$enquiry) {
            return redirect()->route('enquiries')->with('error', 'Enquiry not found.');
        }

        $subscriber = User::find($enquiry->subscriber_id);
        $defaultPlace = trim(($subscriber->city ?? '').', '.($subscriber->country ?? ''), ', ');
        $countries = $this->getSubscriberCountryOptions(
            (int) $enquiry->subscriber_id,
            [$enquiry->country_pref_1, $enquiry->country_pref_2, $enquiry->country_pref_3]
        );

        $formatToSubscriberDate = static function ($dateValue) {
            if (empty($dateValue)) {
                return $dateValue;
            }

            try {
                return Carbon::parse($dateValue)->format('m-d-Y');
            } catch (\Throwable $e) {
                return $dateValue;
            }
        };

        $enquiry->dob = $formatToSubscriberDate($enquiry->dob);
        $enquiry->test_date = $formatToSubscriberDate($enquiry->test_date);
        $enquiry->form_date = $formatToSubscriberDate($enquiry->form_date);

        foreach ($enquiry->refusalHistory as $refusal) {
            $refusal->refusal_date = $formatToSubscriberDate($refusal->refusal_date);
        }

        foreach ($enquiry->workExperience as $experience) {
            $experience->joining_date = $formatToSubscriberDate($experience->joining_date);
            $experience->to_date = $formatToSubscriberDate($experience->to_date);
        }

        foreach ($enquiry->children as $child) {
            $child->child_dob = $formatToSubscriberDate($child->child_dob);
        }

        $enquiryFormSections = app(EnquiryFormSettingsService::class)->resolveSections($subscriber);

        return view('web.create_lead', [
            'subscriberId' => $enquiry->subscriber_id,
            'enquiry' => $enquiry,
            'isEdit' => true,
            'defaultPlace' => $defaultPlace,
            'countries' => $countries,
            'allCountries' => Countries::orderBy('country_name', 'asc')->get(),
            'visaCategories' => app(CountryCategorySettingsService::class)->resolveVisaCategoryNames($subscriber),
            'leadSources' => app(LeadEnquiryService::class)->sources(),
            'leadStatuses' => app(LeadEnquiryService::class)->statuses(),
            'enquiryFormSections' => $enquiryFormSections,
        ]);
    }

    public function updateEnquiry(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact_no' => 'required|phone_intl',
            'dob' => ['nullable', function ($attribute, $value, $fail) {
                $normalizedDob = $this->normalizeDateValue($value);
                if ($value !== null && trim((string) $value) !== '' && $normalizedDob === null) {
                    $fail('The date of birth is not a valid date.');
                    return;
                }
                if ($normalizedDob !== null && Carbon::parse($normalizedDob)->isAfter(Carbon::today())) {
                    $fail('The date of birth cannot be in the future.');
                }
            }],
            'country_pref' => 'required|array|min:1',
            'country_pref.0' => 'required|string|max:255',
            'country_pref.*' => 'nullable|string|max:255|distinct',
            'visa_category' => 'required|string|max:255',
            'address' => 'required|string|min:3|max:1000',
            'postcode' => 'nullable|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
            'country' => 'required|string|max:255',
            'spouse_apply_together' => 'nullable|boolean',
            'spouse_age' => 'nullable|integer|min:0|max:120',
            'spouse_qualification' => 'nullable|string|max:255',
            'spouse_work_experience_years' => 'nullable|numeric|min:0|max:80',
        ]);

        $enquiry = VisaEnquiry::find($id);

        if (!$enquiry) {
            return redirect()->route('enquiries')->with('error', 'Enquiry not found.');
        }

        $subscriber = User::find($enquiry->subscriber_id);
        if ($subscriber) {
            $ccErrors = app(CountryCategorySettingsService::class)->validateEnquirySelection(
                $subscriber,
                $request->country_pref ?? [],
                $request->visa_category
            );
            if (!empty($ccErrors)) {
                return back()->withInput()->withErrors($ccErrors);
            }
        }

        DB::beginTransaction();

        try {
            [$countryPref1, $countryPref2, $countryPref3] = $this->normalizeCountryPreferences($request->country_pref);

            $enquiryData = [
                'full_name' => $request->full_name,
                'dob' => $this->normalizeDateValue($request->dob),
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'marital_status' => $request->marital_status,
                'address' => $request->address,
                'postcode' => $request->postcode,
                'country' => $request->country,
                'country_pref_1' => $countryPref1,
                'country_pref_2' => $countryPref2,
                'country_pref_3' => $countryPref3,
                'visa_category' => $request->visa_category,
                'qualification' => $request->qualification,
                'institution' => $request->institution,
                'passing_year' => $request->passing_year,
                'grade' => $request->grade,
                'english_test' => $request->english_test,
                'overall_score' => $request->overall_score,
                'test_date' => $this->normalizeDateValue($request->test_date),
                'spouse_name' => $request->spouse_name,
                'spouse_apply_together' => $request->boolean('spouse_apply_together'),
                'spouse_age' => $request->spouse_age,
                'spouse_qualification' => $request->spouse_qualification,
                'spouse_work_experience_years' => $request->spouse_work_experience_years,
                'spouse_email' => null,
                'spouse_dob' => null,
                'spouse_contact' => null,
                'signature' => $request->signature ?: $enquiry->signature,
            ];

            $visaEnquiryColumns = array_flip(
                $enquiry->getConnection()->getSchemaBuilder()->getColumnListing('visa_enquiries')
            );

            if (isset($visaEnquiryColumns['form_date'])) {
                $enquiryData['form_date'] = $this->normalizeDateValue($request->form_date);
            }

            if (isset($visaEnquiryColumns['place'])) {
                $enquiryData['place'] = $request->place;
            }

            if (isset($visaEnquiryColumns['print_name'])) {
                $enquiryData['print_name'] = $request->print_name;
            } elseif (isset($visaEnquiryColumns['sign_name'])) {
                $enquiryData['sign_name'] = $request->print_name;
            }

            if (isset($visaEnquiryColumns['consent_to_store_data']) && $request->has('consent_to_store_data')) {
                $enquiryData['consent_to_store_data'] = $request->boolean('consent_to_store_data');
            }

            $leadService = app(LeadEnquiryService::class);
            if (isset($visaEnquiryColumns['lead_source']) && $request->filled('lead_source')) {
                $enquiryData['lead_source'] = $leadService->normalizeSource($request->lead_source, (string) ($enquiry->lead_source ?: 'Walk-in'));
            }

            if (isset($visaEnquiryColumns['lead_status']) && $request->filled('lead_status')) {
                $enquiryData['lead_status'] = $leadService->normalizeStatus($request->lead_status, (string) ($enquiry->lead_status ?: 'Open'));
            }

            if (isset($visaEnquiryColumns['lead_worked_by_user_id']) || isset($visaEnquiryColumns['lead_worked_at'])) {
                $leadFieldsChanged = $request->filled('lead_source') || $request->filled('lead_status');
                if ($leadFieldsChanged) {
                    $enquiryData['lead_worked_by_user_id'] = Auth::id();
                    $enquiryData['lead_worked_at'] = now();
                }
            }

            $enquiry->update($enquiryData);

            EnquiryResidencyHistory::where('enquiry_id', $enquiry->id)->delete();
            if ($request->res_country) {
                foreach ($request->res_country as $key => $country) {
                    if (empty($country)) {
                        continue;
                    }
                    EnquiryResidencyHistory::create([
                        'enquiry_id' => $enquiry->id,
                        'country' => $country,
                        'duration' => $request->res_duration[$key] ?? null,
                        'visa_category' => $request->res_visa[$key] ?? null
                    ]);
                }
            }

            EnquiryTravelHistory::where('enquiry_id', $enquiry->id)->delete();
            if ($request->travel_country) {
                foreach ($request->travel_country as $key => $country) {
                    if (empty($country)) {
                        continue;
                    }
                    EnquiryTravelHistory::create([
                        'enquiry_id' => $enquiry->id,
                        'country' => $country,
                        'duration' => $request->travel_duration[$key] ?? null
                    ]);
                }
            }

            EnquiryRefusalHistory::where('enquiry_id', $enquiry->id)->delete();
            $refusalDates = $this->normalizeDateArray($request->refusal_date ?? []);
            if ($request->refusal_country) {
                foreach ($request->refusal_country as $key => $country) {
                    if (empty($country)) {
                        continue;
                    }
                    EnquiryRefusalHistory::create([
                        'enquiry_id' => $enquiry->id,
                        'country' => $country,
                        'refusal_date' => $refusalDates[$key] ?? null,
                        'refusal_reason' => $request->refusal_reason[$key] ?? null
                    ]);
                }
            }

            EnquiryWorkExperience::where('enquiry_id', $enquiry->id)->delete();
            $joiningDates = $this->normalizeDateArray($request->joining_date ?? []);
            $toDates = $this->normalizeDateArray($request->to_date ?? []);
            if ($request->job_title) {
                foreach ($request->job_title as $key => $job) {
                    if (empty($job)) {
                        continue;
                    }
                    EnquiryWorkExperience::create([
                        'enquiry_id' => $enquiry->id,
                        'job_title' => $job,
                        'employer' => $request->employer[$key] ?? null,
                        'work_country' => $request->work_country[$key] ?? null,
                        'joining_date' => $joiningDates[$key] ?? null,
                        'to_date' => $toDates[$key] ?? null
                    ]);
                }
            }

            EnquiryChild::where('enquiry_id', $enquiry->id)->delete();
            $childDobs = $this->normalizeDateArray($request->child_dob ?? []);
            if ($request->child_name) {
                foreach ($request->child_name as $key => $child) {
                    if (empty($child)) {
                        continue;
                    }
                    EnquiryChild::create([
                        'enquiry_id' => $enquiry->id,
                        'child_name' => $child,
                        'child_age' => $request->child_age[$key] ?? null,
                        'child_gender' => $request->child_relation[$key] ?? ($request->child_gender[$key] ?? null),
                        'child_dob' => $childDobs[$key] ?? null,
                        'apply_together' => !empty($request->child_apply_together[$key]) ? 1 : 0
                    ]);
                }
            }

            EnquiryFundingSource::where('enquiry_id', $enquiry->id)->delete();
            if ($request->funding) {
                foreach ($request->funding as $fund) {
                    EnquiryFundingSource::create([
                        'enquiry_id' => $enquiry->id,
                        'funding_source' => $fund
                    ]);
                }
            }

            $subscriber = User::find($enquiry->subscriber_id);
            $activityUser = Auth::user();
            $activity = new Activities();
            $activity->subscriber_id = $enquiry->subscriber_id;
            $activity->user_id = $activityUser->id ?? $enquiry->subscriber_id;
            $activity->user_name = $activityUser->name ?? ($subscriber->name ?? 'Subscriber');
            $activity->activity_name = "Enquiry Updated";
            $activity->activity_detail = "Enquiry {$enquiry->full_name} updated at " . now()->format('d M, Y H:i:s');
            $activity->activity_icon = "user.png";
            $activity->save();

            DB::commit();

            return redirect()->route('enquiries')->with('success', 'Enquiry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enquiry update failed', [
                'enquiry_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong while updating enquiry.');
        }
    }

    public function appointmentRecords(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['records' => []], 401);
        }

        try {
            $appointmentService = app(AppointmentService::class);
            $appointmentService->ensureTableExists();
            $subscriberId = $appointmentService->resolveSubscriberId($user);

            return response()
                ->json([
                    'records' => $this->appointmentRecordsForSubscriber($subscriberId)
                        ->map(fn (Appointment $appointment) => $this->formatAppointmentRecord($appointment))
                        ->values(),
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'records' => [],
                'message' => $exception->getMessage() ?: 'Failed to load appointment records.',
            ], 500);
        }
    }

    public function getAppointmentRecords(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $appointmentService = app(AppointmentService::class);
            $appointmentService->ensureTableExists();
            $subscriberId = $appointmentService->resolveSubscriberId($user);

            $query = Appointment::query()
                ->where('subscriber_id', $subscriberId)
                ->whereNotNull('appointment_date')
                ->with(['client', 'user']);

            return DataTables::eloquent($query)
                ->addColumn('client_name', function (Appointment $row) {
                    return optional($row->client)->name ?? 'N/A';
                })
                ->addColumn('appointment_date', fn (Appointment $row) => $row->formattedDate())
                ->addColumn('appointment_time', fn (Appointment $row) => $row->formattedTime())
                ->editColumn('remarks', fn (Appointment $row) => $row->remarks ?? 'N/A')
                ->addColumn('status', function (Appointment $row) {
                    $status = strtolower((string) ($row->status ?: 'pending'));
                    $statusClass = $this->appointmentStatusClass($status);
                    $statusLabel = $this->formatAppointmentStatusLabel($status);

                    return '<span class="badge bg-' . $statusClass . '">' . e($statusLabel) . '</span>';
                })
                ->addColumn('sent_by', function (Appointment $row) {
                    return optional($row->user)->name ?? 'N/A';
                })
                ->addColumn('sent_on', function (Appointment $row) {
                    return $row->created_at
                        ? Carbon::parse($row->created_at)->format('d-m-Y H:i:s')
                        : 'N/A';
                })
                ->addColumn('action', function () {
                    return '<button type="button" class="btn btn-link p-0 m-0 appointment-record-view-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="View" aria-label="View appointment">'
                        . '<i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></button>';
                })
                ->filterColumn('client_name', function ($query, $keyword) {
                    $query->whereHas('client', function ($clientQuery) use ($keyword) {
                        $clientQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('sent_by', function ($query, $keyword) {
                    $query->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('sent_on', function ($query, $keyword) {
                    $query->whereRaw("DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') like ?", ['%' . $keyword . '%']);
                })
                ->orderColumn('appointment_date', function ($query, $order) {
                    $query->orderBy('appointment_date', $order);
                })
                ->orderColumn('appointment_time', function ($query, $order) {
                    $query->orderBy('appointment_time', $order);
                })
                ->orderColumn('sent_on', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->orderColumn('id', function ($query, $order) {
                    $query->orderBy('id', $order);
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'draw' => (int) $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $exception->getMessage() ?: 'Failed to load appointment records.',
            ], 500);
        }
    }

    private function appointmentRecordsForSubscriber(int $subscriberId)
    {
        return Appointment::where('subscriber_id', $subscriberId)
            ->whereNotNull('appointment_date')
            ->with(['client', 'user'])
            ->orderByRaw('TIMESTAMP(appointment_date, appointment_time) DESC')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    private function formatAppointmentStatusLabel(string $status): string
    {
        return match ($status) {
            'accepted' => 'Confirmed',
            'denied' => 'Denied',
            'canceled' => 'Cancelled',
            'completed' => 'Completed',
            default => 'Pending',
        };
    }

    private function appointmentStatusClass(string $status): string
    {
        return match ($status) {
            'accepted' => 'success',
            'denied', 'canceled' => 'danger',
            'completed' => 'info',
            default => 'warning',
        };
    }

    private function formatAppointmentRecord(Appointment $appointment): array
    {
        $status = strtolower((string) ($appointment->status ?: 'pending'));

        return [
            'id' => $appointment->id,
            'client_name' => optional($appointment->client)->name ?? 'N/A',
            'appointment_date' => $appointment->formattedDate(),
            'appointment_time' => $appointment->formattedTime(),
            'remarks' => $appointment->remarks ?? 'N/A',
            'status' => $this->formatAppointmentStatusLabel($status),
            'status_class' => $this->appointmentStatusClass($status),
            'sent_by' => optional($appointment->user)->name ?? 'N/A',
            'sent_on' => $appointment->created_at
                ? Carbon::parse($appointment->created_at)->format('d-m-Y H:i:s')
                : 'N/A',
        ];
    }

    public function storeAppointment(Request $request)
    {
        try {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'client_email' => 'nullable|email',
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i',
                'remarks' => 'required|string|max:500',
                'send_via' => 'required|in:email',
            ]);

            $user = Auth::user();
            $appointmentService = app(AppointmentService::class);
            $appointmentService->ensureTableExists();
            $subscriberId = $appointmentService->resolveSubscriberId($user);

            $client = Clients::where('id', $request->client_id)
                ->where('subscriber_id', $subscriberId)
                ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a valid client from your account.',
                ], 422);
            }

            $clientEmail = $request->filled('client_email') ? $request->client_email : $client->email;
            if (empty($clientEmail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client email is required for email notifications.',
                ], 422);
            }

            $timezone = config('app.timezone');
            $scheduledAt = Appointment::scheduledAtFromInput(
                $request->appointment_date,
                $request->appointment_time,
                $timezone
            );

            if (!$scheduledAt || $scheduledAt->lte(now($timezone))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment must be scheduled for a future date and time.',
                ], 422);
            }

            $appointment = new Appointment();
            $appointment->client_id = $client->id;
            $appointment->subscriber_id = $subscriberId;
            $appointment->user_id = Auth::id();
            $appointment->appointment_date = Carbon::parse($request->appointment_date)->toDateString();
            $appointment->appointment_time = Carbon::createFromFormat('H:i', $request->appointment_time)->format('H:i:s');
            $appointment->remarks = $request->remarks;
            $appointment->send_via = $request->send_via;
            $appointment->status = 'pending';
            $appointment->calendly_link = null;
            $appointment->calendly_event_uri = null;
            $appointment->save();

            $responseLinks = $this->createAppointmentResponseLinks($appointment, $clientEmail);
            $appointment->setAttribute('accept_url', $responseLinks['accept_url']);
            $appointment->setAttribute('decline_url', $responseLinks['decline_url']);

            $appointment->load(['client', 'user']);
            $formattedAppointment = $this->formatAppointmentRecord($appointment);

            try {
                BrandedMail::sendWithAlertsArchive(
                    $clientEmail,
                    fn () => new AppointmentSchedulerMail($appointment, $client, $user)
                );

                return response()->json([
                    'success' => true,
                    'email_sent' => true,
                    'message' => 'Appointment invitation sent successfully.',
                    'appointment' => $formattedAppointment,
                ]);
            } catch (\Throwable $mailException) {
                Log::error('Appointment scheduled but notification failed to send', [
                    'appointment_id' => $appointment->id,
                    'client_id' => $client->id,
                    'error' => $mailException->getMessage(),
                ]);

                return response()->json([
                    'success' => true,
                    'email_sent' => false,
                    'message' => 'Appointment saved, but the invitation email could not be sent. Please verify mail settings and try again.',
                    'appointment' => $formattedAppointment,
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            throw $validationException;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Failed to schedule appointment.',
            ], 500);
        }
    }

    private function createAppointmentResponseLinks(Appointment $appointment, ?string $clientEmail): array
    {
        $scheduledAt = $appointment->scheduledAt(config('app.timezone'));
        $expiresAt = now()->addDays(30);
        if ($scheduledAt && $scheduledAt->copy()->addDays(7)->gt($expiresAt)) {
            $expiresAt = $scheduledAt->copy()->addDays(7);
        }

        $routeParams = ['appointment' => $appointment->id];
        if (!empty($clientEmail)) {
            $routeParams['email'] = $clientEmail;
        }

        return [
            'accept_url' => URL::temporarySignedRoute('appointment.respond', $expiresAt, array_merge($routeParams, ['action' => 'accept'])),
            'decline_url' => URL::temporarySignedRoute('appointment.respond', $expiresAt, array_merge($routeParams, ['action' => 'decline'])),
        ];
    }

    private function createCalendlySchedulingLinkForAppointment(
        Appointment $appointment,
        ?string $clientEmail,
        User $sender
    ): array {
        // ✅ Guard: check status first, before any API calls
        if ($appointment->status === 'completed') {
            return ['success' => false, 'message' => 'Appointment already completed.'];
        }

        // ✅ Define $token before using it
        $token = config('services.calendly.pat');
        if (empty($token)) {
            return ['success' => false, 'message' => 'Calendly PAT not configured.'];
        }

        $baseUrl = rtrim(config('services.calendly.base_url', 'https://api.calendly.com'), '/');

        $startDateTime = $appointment->scheduledAt($sender->timezone ?? config('app.timezone'));
        if (!$startDateTime) {
            return ['success' => false, 'message' => 'Appointment date and time are required.'];
        }

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ];

        // ✅ try block is now correctly INSIDE the function
        try {
            $meResponse = Http::withHeaders($headers)->get($baseUrl . '/users/me');
            if (!$meResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Unable to connect with Calendly. Please verify your Calendly token.',
                ];
            }

            $ownerUri = $meResponse->json('resource.uri');
            if (!$ownerUri) {
                return [
                    'success' => false,
                    'message' => 'Calendly user details are incomplete. Please reconnect Calendly.',
                ];
            }

            $eventPayload = [
                'name'     => substr('Confirmed Appointment with ' . $sender->name, 0, 55),
                'host'     => $ownerUri,
                'duration' => 30,
                'timezone' => $sender->timezone ?? config('app.timezone'),
                'date_setting' => [
                    'type'       => 'date_range',
                    'start_date' => $startDateTime->toDateString(),
                    'end_date'   => $startDateTime->toDateString(),
                ],
                'location' => ['kind' => 'ask_invitee'],
            ];

            $oneOffResponse = Http::withHeaders($headers)
                ->post($baseUrl . '/one_off_event_types', $eventPayload);

            if (!$oneOffResponse->successful()) {
                Log::error('Calendly one_off_event_types failed', [
                    'response' => $oneOffResponse->json(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Unable to create a Calendly confirmation link right now.',
                ];
            }

            $eventTypeUri = $oneOffResponse->json('resource.uri');

            $linkPayload = [
                'owner'           => $eventTypeUri,
                'owner_type'      => 'EventType',
                'max_event_count' => 1,
            ];

            if (!empty($clientEmail)) {
                $linkPayload['invitee_email'] = $clientEmail;
            }

            $schedulingLinkResponse = Http::withHeaders($headers)
                ->post($baseUrl . '/scheduling_links', $linkPayload);

            if (!$schedulingLinkResponse->successful()) {
                Log::error('Calendly scheduling_links failed', [
                    'response' => $schedulingLinkResponse->json(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Unable to generate a Calendly confirmation link right now.',
                ];
            }

            return [
                'success'        => true,
                'booking_url'    => $schedulingLinkResponse->json('resource.booking_url'),
                'event_type_uri' => $eventTypeUri,
            ];

        } catch (\Throwable $e) {
            Log::error('Calendly appointment acceptance error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Unable to prepare Calendly confirmation at the moment.',
            ];
        }
    }

    public function respondToAppointment(Request $request, Appointment $appointment, string $action)
    {
        if (!in_array($action, ['accept', 'decline'])) {
            abort(404);
        }

        if ($appointment->status === 'completed') {
            return view('web.appointment_response', [
                'title' => 'Appointment Already Completed',
                'subtitle' => 'This appointment is already marked as completed.',
                'status' => 'neutral',
                'calendlyUrl' => null,
            ]);
        }

        $client = Clients::find($appointment->client_id);
        $sender = User::find($appointment->user_id);
        $inviteEmail = trim((string) $request->query('email', $client?->email ?? ''));
        $targetStatus = $action === 'accept'
            ? Appointment::STATUS_ACCEPTED
            : Appointment::STATUS_DENIED;
        $choice = trim((string) $request->query('choice', ''));

        if ($appointment->status === $targetStatus) {
            return view('web.appointment_response', [
                'title' => $action === 'accept' ? 'Appointment Already Accepted' : 'Appointment Already Declined',
                'subtitle' => 'Your response was already recorded for this appointment.',
                'status' => $action === 'accept' ? 'accepted' : 'declined',
                'calendlyUrl' => $action === 'accept' ? $appointment->calendly_link : null,
            ]);
        }

        if (in_array($appointment->status, [Appointment::STATUS_ACCEPTED, Appointment::STATUS_DENIED, Appointment::STATUS_CANCELED], true)) {
            return view('web.appointment_response', [
                'title' => 'Response Already Recorded',
                'subtitle' => 'This appointment invitation has already been answered.',
                'status' => 'neutral',
                'calendlyUrl' => null,
            ]);
        }

        if ($appointment->status === 'pending' && $appointment->isPast()) {
            return view('web.appointment_response', [
                'title' => 'Response Window Closed',
                'subtitle' => 'This appointment time has already passed. Please contact your consultant to schedule a new appointment.',
                'status' => 'neutral',
                'calendlyUrl' => null,
            ]);
        }

        if ($appointment->status === 'pending'
            && $this->isAppointmentResponseCutoffReached($appointment)
            && !in_array($choice, ['dont_notify', 'seek_next'], true)) {
            return view('web.appointment_response_cutoff', [
                'title' => 'Oops!',
                'subtitle' => 'Cut-off time has reached for this appointment.',
                'prompt' => 'Do you still want to notify the consultant or seek another appointment?',
                'dontNotifyUrl' => $this->signedAppointmentResponseUrl($appointment, $action, $inviteEmail, 'dont_notify'),
                'seekNextUrl' => $this->signedAppointmentResponseUrl($appointment, $action, $inviteEmail, 'seek_next'),
            ]);
        }

        if ($choice === 'seek_next') {
            return $this->finalizeAppointmentSeekNext($appointment, $client, $sender, $inviteEmail, $action);
        }

        $notifyConsultant = $choice !== 'dont_notify';

        if ($action === 'accept') {
            $appointment->status = Appointment::STATUS_ACCEPTED;
            $appointment->save();

            $calendlyUrl = null;
            if ($sender) {
                $calendly = $this->createCalendlySchedulingLinkForAppointment($appointment, $inviteEmail ?: $client?->email, $sender);
                if ($calendly['success']) {
                    $appointment->calendly_link = $calendly['booking_url'];
                    $appointment->calendly_event_uri = $calendly['event_type_uri'];
                    $appointment->save();
                    $calendlyUrl = $calendly['booking_url'];
                }
            }

            $this->recordAppointmentClientResponse($appointment, $client, $sender, 'accepted');
            if ($notifyConsultant) {
                $this->notifyAppointmentResponse($appointment, $client, $sender, 'accepted');
            }

            return view('web.appointment_response', [
                'title' => 'Thank You! Appointment Accepted',
                'subtitle' => $notifyConsultant
                    ? 'Your response has been recorded successfully. The consultant has been notified.'
                    : 'Your response has been recorded successfully.',
                'status' => 'accepted',
                'calendlyUrl' => $calendlyUrl,
            ]);
        }

        $appointment->status = Appointment::STATUS_DENIED;
        $appointment->save();

        $this->recordAppointmentClientResponse($appointment, $client, $sender, 'declined');
        if ($notifyConsultant) {
            $this->notifyAppointmentResponse($appointment, $client, $sender, 'declined');
        }

        return view('web.appointment_response', [
            'title' => 'Appointment Declined',
            'subtitle' => $notifyConsultant
                ? 'You have declined this appointment. The sender has been notified.'
                : 'You have declined this appointment.',
            'status' => 'declined',
            'calendlyUrl' => null,
        ]);
    }

    private function isAppointmentResponseCutoffReached(Appointment $appointment): bool
    {
        $scheduledAt = $appointment->scheduledAt(config('app.timezone'));

        if (!$scheduledAt || $appointment->isPast()) {
            return false;
        }

        return now()->gte($scheduledAt->copy()->subHour());
    }

    private function signedAppointmentResponseUrl(
        Appointment $appointment,
        string $action,
        string $inviteEmail,
        string $choice
    ): string {
        $routeParams = [
            'appointment' => $appointment->id,
            'action' => $action,
            'choice' => $choice,
        ];

        if ($inviteEmail !== '') {
            $routeParams['email'] = $inviteEmail;
        }

        $scheduledAt = $appointment->scheduledAt(config('app.timezone'));
        $expiresAt = now()->addDay();
        if ($scheduledAt && $scheduledAt->copy()->addDays(7)->gt($expiresAt)) {
            $expiresAt = $scheduledAt->copy()->addDays(7);
        }

        return URL::temporarySignedRoute('appointment.respond', $expiresAt, $routeParams);
    }

    private function finalizeAppointmentSeekNext(
        Appointment $appointment,
        ?Clients $client,
        ?User $sender,
        string $inviteEmail,
        string $originalAction
    ) {
        $appointment->status = Appointment::STATUS_CANCELED;
        $appointment->save();

        $this->recordAppointmentClientResponse($appointment, $client, $sender, 'declined', true);
        $this->notifyAppointmentSeekNext($appointment, $client, $sender, $originalAction);

        return view('web.appointment_response', [
            'title' => 'Request Received',
            'subtitle' => 'Your request to seek the next available appointment has been recorded. The consultant will be in touch.',
            'status' => 'neutral',
            'calendlyUrl' => null,
        ]);
    }

    private function recordAppointmentClientResponse(
        Appointment $appointment,
        ?Clients $client,
        ?User $sender,
        string $response,
        bool $seekNextAppointment = false
    ): void {
        if (!$client || !$sender) {
            return;
        }

        $appointmentDate = $appointment->formattedDate();
        $appointmentTime = $appointment->formattedTime();

        if ($seekNextAppointment) {
            $discussion = 'Client responded after the cut-off time and requested to seek the next available appointment.'
                . ' Original slot: ' . $appointmentDate . ' at ' . $appointmentTime . '.';
        } elseif ($response === 'accepted') {
            $discussion = 'Client accepted the appointment invitation for '
                . $appointmentDate . ' at ' . $appointmentTime . '.';
        } else {
            $discussion = 'Client declined the appointment invitation for '
                . $appointmentDate . ' at ' . $appointmentTime . '.';
        }

        if (!empty($appointment->remarks)) {
            $discussion .= ' Purpose: ' . $appointment->remarks;
        }

        $applicationId = Applications::where('client_id', $client->id)
            ->where('subscriber_id', $appointment->subscriber_id)
            ->orderByDesc('created_at')
            ->value('application_id');

        Client_discussions::create([
            'subscriber_id' => $appointment->subscriber_id,
            'user_id' => $sender->id,
            'user_name' => $sender->name,
            'client_id' => $client->id,
            'client_name' => $client->name,
            'application_id' => $applicationId,
            'communication_type' => 'E-meet',
            'communication_date' => $appointment->scheduledAt($sender->timezone ?? config('app.timezone')),
            'discussion' => $discussion,
        ]);
    }

    private function notifyAppointmentResponse(
        Appointment $appointment,
        ?Clients $client,
        ?User $sender,
        string $response
    ): void {
        if (!$client || !$sender) {
            return;
        }

        $accepted = $response === 'accepted';
        $appointmentDate = $appointment->formattedDate();
        $appointmentTime = $appointment->formattedTime();
        $title = $accepted
            ? sprintf('%s accepted appointment on %s at %s', $client->name, $appointmentDate, $appointmentTime)
            : sprintf('%s declined appointment on %s at %s', $client->name, $appointmentDate, $appointmentTime);
        $body = $accepted
            ? 'The client confirmed the proposed appointment. View details in Appointment Scheduler.'
            : 'The client declined the proposed appointment. View details in Appointment Scheduler.';

        try {
            if (!empty($sender->email)) {
                BrandedMail::sendWithAlertsArchive(
                    $sender->email,
                    fn () => new AppointmentResponseMail($appointment, $client, $sender, $response)
                );
            }
        } catch (\Throwable $e) {
            Log::error('Appointment response email failed', [
                'appointment_id' => $appointment->id,
                'response' => $response,
                'error' => $e->getMessage(),
            ]);
        }

        app(NotificationService::class)->notifyUser(
            $sender,
            'meeting_reminders',
            $title,
            $body,
            route('my_settings') . '#appointment',
            [
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'response' => $response,
            ]
        );
    }

    private function notifyAppointmentSeekNext(
        Appointment $appointment,
        ?Clients $client,
        ?User $sender,
        string $originalAction
    ): void {
        if (!$client || !$sender) {
            return;
        }

        $appointmentDate = $appointment->formattedDate();
        $appointmentTime = $appointment->formattedTime();
        $title = sprintf('%s requested the next appointment after cut-off', $client->name);
        $body = sprintf(
            'The client responded after the cut-off time for the appointment on %s at %s and requested to seek the next available appointment.',
            $appointmentDate,
            $appointmentTime
        );

        try {
            if (!empty($sender->email)) {
                BrandedMail::sendWithAlertsArchive(
                    $sender->email,
                    fn () => new AppointmentResponseMail($appointment, $client, $sender, 'seek_next')
                );
            }
        } catch (\Throwable $e) {
            Log::error('Appointment seek-next email failed', [
                'appointment_id' => $appointment->id,
                'original_action' => $originalAction,
                'error' => $e->getMessage(),
            ]);
        }

        app(NotificationService::class)->notifyUser(
            $sender,
            'meeting_reminders',
            $title,
            $body,
            route('my_settings') . '#appointment',
            [
                'appointment_id' => $appointment->id,
                'client_id' => $client->id,
                'response' => 'seek_next',
            ]
        );
    }

    private function sendAppointmentSms(string $phone, string $clientName, string $senderName, string $acceptLink, string $declineLink, ?string $remarks, string $appointmentDate, string $appointmentTime, string $timezone): array
    {
        $message = "Dear {$clientName},\n\n".
            "{$senderName} has invited you for an appointment on {$appointmentDate} at {$appointmentTime} ({$timezone}).\n\n".
            "Accept: {$acceptLink}\n".
            "Decline: {$declineLink}\n\n".
            (!empty($remarks) ? "Meeting purpose: {$remarks}\n\n" : '').
            "Please confirm by accepting or declining the appointment using the links above.\n\n".
            BrandedMail::emailSignaturePlain();

        $smsUrl = config('services.sms_gateway.url');
        $smsToken = config('services.sms_gateway.token');

        if (function_exists('send_sms')) {
            try {
                $existingResult = send_sms($phone, $message);

                if ($existingResult === false) {
                    return [
                        'sent' => false,
                        'message' => 'Appointment link created successfully, but SMS could not be delivered.',
                    ];
                }

                return ['sent' => true, 'message' => null];
            } catch (\Throwable $e) {
                Log::warning('Existing SMS integration exception', ['error' => $e->getMessage()]);
            }
        }

        if (empty($smsUrl)) {
            return [
                'sent' => false,
                'message' => 'Appointment link created successfully. Email sent. SMS gateway is not configured.',
            ];
        }

        try {
            $response = Http::withToken($smsToken)->post($smsUrl, [
                'phone' => $phone,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('SMS gateway request failed', ['response' => $response->body()]);

                return [
                    'sent' => false,
                    'message' => 'Appointment link created successfully, but SMS could not be delivered.',
                ];
            }

            return ['sent' => true, 'message' => null];
        } catch (\Throwable $e) {
            Log::warning('SMS gateway exception', ['error' => $e->getMessage()]);

            return [
                'sent' => false,
                'message' => 'Appointment link created successfully, but SMS could not be delivered.',
            ];
        }
    }


    public function saveEmailTemplate(Request $request, EmailTemplateService $emailTemplateService)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'template_key' => 'required|string|max:100',
            'template_name' => 'required|string|max:191',
            'custom_name' => 'nullable|string|max:191',
            'subject' => 'nullable|string|max:191',
            'body' => 'nullable|string',
        ]);

        $validated['audience'] = strtolower($user->user_type) === 'admin' ? 'admin' : 'subscriber';

        $emailTemplateService->saveTemplate($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Email template saved successfully.',
        ]);
    }

    public function getEmailTemplates(EmailTemplateService $emailTemplateService)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $audience = strtolower($user->user_type) === 'admin' ? 'admin' : 'subscriber';
        $templates = $emailTemplateService->getTemplatesForSettings($user);
        $rows = $templates[$audience]->map(function ($template) {
            return [
                'audience' => $template->audience,
                'template_key' => $template->template_key,
                'template_name' => $template->template_name,
                'custom_name' => $template->custom_name,
                'subject' => $template->subject,
                'body' => $template->body,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'audience' => $audience,
            'templates' => $rows,
        ]);
    }


    public function savePaymentReminderSettings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $subscriberId = $user->user_type === 'Subscriber' ? $user->id : (int) $user->added_by;
        $reminderType = $request->input('reminder_type', PaymentReminderSetting::TYPE_PAYMENTS);

        if (!in_array($reminderType, PaymentReminderSetting::allowedScheduleTypes(), true)) {
            return response()->json(['success' => false, 'message' => 'Invalid reminder type.'], 422);
        }

        if ($reminderType === PaymentReminderSetting::TYPE_DOCUMENTS) {
            if (!PaymentReminderSetting::hasReminderTypeColumn()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents reminder settings are unavailable until the latest database update is applied.',
                ], 503);
            }

            $validated = $request->validate([
                'email_frequency' => 'required|in:daily,weekly,monthly,quarterly',
                'email_to' => 'required|in:' . implode(',', PaymentReminderSetting::allowedEmailToValuesForRemindersTo(PaymentReminderSetting::REMINDERS_TO_CLIENTS)),
            ]);

            PaymentReminderSetting::saveForType(
                $subscriberId,
                PaymentReminderSetting::TYPE_DOCUMENTS,
                [
                    'reminders_to' => PaymentReminderSetting::REMINDERS_TO_CLIENTS,
                    'client_group' => PaymentReminderSetting::CLIENT_GROUP_ALL,
                    'email_frequency' => $validated['email_frequency'],
                    'email_to' => $validated['email_to'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Documents reminder settings saved successfully.',
            ]);
        }

        $remindClients = $request->boolean('remind_clients');
        $remindAssociates = $request->boolean('remind_associates');

        if (!$remindClients && !$remindAssociates) {
            return response()->json([
                'success' => false,
                'message' => 'Select at least one reminder audience (Clients or Associates).',
            ], 422);
        }

        if ($remindClients && $remindAssociates && !PaymentReminderSetting::hasEmailToAssociatesColumn()) {
            return response()->json([
                'success' => false,
                'message' => 'Selecting both Clients and Associates requires the latest database update. Please run migrations.',
            ], 503);
        }

        $rules = [
            'remind_clients' => 'nullable|boolean',
            'remind_associates' => 'nullable|boolean',
            'client_group' => 'required|in:' . implode(',', PaymentReminderSetting::allowedClientGroups()),
            'email_frequency' => 'required|in:daily,weekly,monthly,quarterly',
        ];

        if ($remindClients) {
            $rules['email_to'] = 'required|in:' . implode(',', PaymentReminderSetting::allowedEmailToValuesForRemindersTo(PaymentReminderSetting::REMINDERS_TO_CLIENTS));
        }

        if ($remindAssociates) {
            $rules['email_to_associates'] = 'required|in:' . implode(',', PaymentReminderSetting::allowedEmailToValuesForRemindersTo(PaymentReminderSetting::REMINDERS_TO_ASSOCIATES));
        }

        $validated = $request->validate($rules);

        $remindersTo = PaymentReminderSetting::remindersToFromAudienceFlags([
            'remind_clients' => $remindClients,
            'remind_associates' => $remindAssociates,
        ]);

        $emailTo = $remindClients
            ? $validated['email_to']
            : PaymentReminderSetting::defaultEmailToForRemindersTo(PaymentReminderSetting::REMINDERS_TO_CLIENTS);
        $emailToAssociates = $remindAssociates
            ? $validated['email_to_associates']
            : null;

        if ($remindersTo === PaymentReminderSetting::REMINDERS_TO_ASSOCIATES) {
            $emailTo = $emailToAssociates;
            $emailToAssociates = null;
        } elseif ($remindersTo === PaymentReminderSetting::REMINDERS_TO_CLIENTS) {
            $emailToAssociates = null;
        }

        PaymentReminderSetting::saveForType(
            $subscriberId,
            PaymentReminderSetting::TYPE_PAYMENTS,
            array_filter([
                'reminders_to' => $remindersTo,
                'client_group' => $validated['client_group'],
                'email_frequency' => $validated['email_frequency'],
                'email_to' => $emailTo,
                'email_to_associates' => $emailToAssociates,
            ], fn ($value, $key) => $key !== 'email_to_associates' || PaymentReminderSetting::hasEmailToAssociatesColumn(), ARRAY_FILTER_USE_BOTH)
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment reminder settings saved successfully.',
        ]);
    }

    public function saveApplicationReminder(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        if (!Schema::hasTable('application_reminders')) {
            return response()->json([
                'success' => false,
                'message' => 'Application reminders are unavailable until the latest database update is applied.',
            ], 503);
        }

        $subscriberId = $user->user_type === 'Subscriber' ? $user->id : (int) $user->added_by;

        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'required|integer|exists:applications,id',
            'subject' => 'required|string|max:255',
            'deadline' => 'required|date',
            'description' => 'nullable|string|max:5000',
            'email_frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'email_to' => 'required|in:' . implode(',', ApplicationReminder::allowedEmailToValues()),
        ]);

        $client = Clients::find($validated['client_id']);
        $application = Applications::find($validated['application_id']);

        if (!$client || (int) $client->subscriber_id !== $subscriberId) {
            return response()->json(['success' => false, 'message' => 'Invalid client selected.'], 422);
        }

        if (!$application || (int) $application->subscriber_id !== $subscriberId || (int) $application->client_id !== (int) $validated['client_id']) {
            return response()->json(['success' => false, 'message' => 'Invalid application selected.'], 422);
        }

        ApplicationReminder::create([
            'user_id' => $subscriberId,
            'client_id' => $validated['client_id'],
            'application_id' => $validated['application_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'] ?? null,
            'deadline' => $validated['deadline'],
            'email_frequency' => $validated['email_frequency'],
            'email_to' => $validated['email_to'],
            'notify_user_id' => $user->id,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application reminder saved successfully.',
        ]);
    }

    public function deleteApplicationReminder($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        if (!Schema::hasTable('application_reminders')) {
            return response()->json([
                'success' => false,
                'message' => 'Application reminders are unavailable until the latest database update is applied.',
            ], 503);
        }

        $subscriberId = $user->user_type === 'Subscriber' ? $user->id : (int) $user->added_by;
        $reminder = ApplicationReminder::where('user_id', $subscriberId)->where('id', $id)->first();

        if (!$reminder) {
            return response()->json(['success' => false, 'message' => 'Reminder not found.'], 404);
        }

        $reminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application reminder deleted successfully.',
        ]);
    }

    public function saveReportSettings(Request $request)
    {
        try {
            $user = Auth::user();

            $allowedModules = [
                'clients',
                'applications',
                'invoices',
                'payments',
                'referrals',
                'wallets',
            ];

            if ($user && strtolower($user->user_type) === 'admin') {
                $allowedModules[] = 'subscribers';
                $allowedModules[] = 'affiliates';
            }

            $request->validate([
                'modules' => 'required|array|min:1',
                'modules.*' => 'in:'.implode(',', $allowedModules),
                'frequency' => 'required|in:daily,weekly,monthly,quarterly',
                'delivery_mode' => 'required|in:attachment,link',
                'emails' => ['required', 'string', 'max:1000']
            ]);

            $rawEmails = trim((string) $request->emails);
            $normalizedInput = str_replace(["\r\n", "\r", "\n", ";"], ',', $rawEmails);
            $emails = array_values(array_filter(array_map('trim', explode(',', $normalizedInput)), function ($email) {
                return $email !== '';
            }));
            $emails = array_slice(array_values(array_unique($emails, SORT_STRING)), 0, 5);

            if (count($emails) === 0) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'emails' => ['Please enter at least one recipient email.']
                    ]
                ], 422);
            }

            $invalidEmails = [];
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = $email;
                }
            }

            if (!empty($invalidEmails)) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'emails' => ['Please enter valid email addresses separated by commas, semicolons, or new lines.']
                    ]
                ], 422);
            }

            $subscriberEmail = trim((string) optional($user)->email);

            if ($subscriberEmail !== '') {
                $emails = array_values(array_filter($emails, function ($email) use ($subscriberEmail) {
                    return strcasecmp($email, $subscriberEmail) !== 0;
                }));
                array_unshift($emails, $subscriberEmail);
            }

            $emails = array_slice(array_values(array_unique($emails, SORT_STRING)), 0, 5);
            $normalizedEmails = implode(', ', $emails);

            $setting = ReportSetting::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'modules' => $request->modules,
                    'frequency' => $request->frequency,
                    'delivery_mode' => $request->delivery_mode,
                    'emails' => $normalizedEmails
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Report settings saved successfully. Reports will be sent according to the selected schedule.',
                'data' => $setting
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while saving report settings.',
                'error' => $e
            ], 500);

        }
    }


    public function downloadScheduledReport(Request $request, $file)
    {
        $safeFile = basename($file);
        $filePath = storage_path('app/reports/' . $safeFile);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }

    public function createManualLead()
    {
        $user = $this->check_login();

        if (membership_access_blocked($user)) {
            return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
        }

        $this->set_timezone();

        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        if ($user->user_type != "admin" && $user->user_type != "Subscriber") {
            if (!$subscriber || membership_access_blocked_for_subscriber($subscriber)) {
                return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
            }
        } elseif ($user->user_type == "Subscriber" && membership_access_blocked($user)) {
            return redirect()->route('membership')->with('membership_expiry', 'Membership has expired.');
        }

        $leadService = app(LeadEnquiryService::class);
        $ccService = app(CountryCategorySettingsService::class);
        $countries = $this->getSubscriberCountryOptions((int) $subscriber->id);
        $allCountries = Countries::orderBy('country_name', 'asc')->get();
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $page = "enquiries";

        return view('web.add_lead', compact(
            'user',
            'subscriber',
            'countries',
            'allCountries',
            'visaCategories',
            'page',
            'leadService'
        ) + [
            'leadSources' => $leadService->sources(),
            'leadStatuses' => $leadService->statuses(),
        ]);
    }

    public function storeManualLead(Request $request)
    {
        $user = $this->check_login();
        $this->set_timezone();

        $subscriber = $user->user_type == "Subscriber" ? $user : User::find($user->added_by);

        if (!$subscriber) {
            return redirect()->route('enquiries')->with('error', 'Subscriber account not found.');
        }

        $leadService = app(LeadEnquiryService::class);
        $allowedSources = $leadService->sources();
        $allowedStatuses = $leadService->statuses();

        $request->validate([
            'full_name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'contact_no' => 'required|phone_intl',
            'country' => 'required|string|max:255',
            'country_pref' => 'required|array|min:1',
            'country_pref.0' => 'required|string|max:255',
            'country_pref.*' => 'nullable|string|max:255|distinct',
            'visa_category' => 'required|string|max:255',
            'lead_source' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::in($allowedSources)],
            'lead_status' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::in($allowedStatuses)],
            'address' => 'nullable|string|max:1000',
            'postcode' => 'nullable|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $ccErrors = app(CountryCategorySettingsService::class)->validateEnquirySelection(
            $subscriber,
            $request->country_pref ?? [],
            $request->visa_category
        );

        if (!empty($ccErrors)) {
            return back()->withInput()->withErrors($ccErrors);
        }

        $address = trim((string) $request->address);
        $remarks = trim((string) $request->remarks);
        if ($address === '' && $remarks !== '') {
            $address = $remarks;
        }
        if ($address === '') {
            $address = 'Not provided';
        }

        $payload = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'country' => $request->country,
            'country_pref' => $request->country_pref,
            'visa_category' => $request->visa_category,
            'address' => $address,
            'postcode' => $request->postcode,
            'lead_source' => $request->lead_source,
            'lead_status' => $request->lead_status,
        ];

        $enquiry = $leadService->createFromExternalSource($payload, (int) $subscriber->id, $user);

        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "New Lead Added";
        $activity->activity_detail = "Lead {$enquiry->full_name} added manually from {$enquiry->lead_source} by {$user->name} at " . ($request->local_time ?: now()->format('d M, Y H:i:s'));
        $activity->activity_icon = "user.png";
        $activity->local_time = $request->local_time;
        $activity->save();

        return redirect()->route('enquiries')->with('success', 'Lead added successfully.');
    }

    public function createLead($id)
    {
        $subscriberId = decrypt($id);
        $subscriber = User::find($subscriberId);
        $ccService = app(CountryCategorySettingsService::class);
        $countries = $this->getSubscriberCountryOptions((int) $subscriberId);
        $allCountries = Countries::orderBy('country_name', 'asc')->get();
        $visaCategories = $ccService->resolveVisaCategoryNames($subscriber);
        $defaultPlace = trim(($subscriber?->city ?? '').', '.($subscriber?->country ?? ''), ', ');
        $leadService = app(LeadEnquiryService::class);
        $defaultLeadSource = $leadService->normalizeSource(
            request()->query('source') ?: request()->query('lead_source'),
            'Walk-in'
        );

        $enquiryFormSections = app(EnquiryFormSettingsService::class)->resolveSectionsForSubscriberId((int) $subscriberId);

        return view('web.create_lead', compact(
            'subscriberId',
            'defaultPlace',
            'countries',
            'allCountries',
            'visaCategories',
            'leadService',
            'defaultLeadSource',
            'enquiryFormSections'
        ));
    }
}
