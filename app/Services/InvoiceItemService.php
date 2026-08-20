<?php

namespace App\Services;

use App\Models\AssociateInvoice;
use App\Models\Internal_Invoices;
use App\Models\InvoiceItem;
use App\Models\Applications;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InvoiceItemService
{
    public function validationRules(bool $requireApplication = false): array
    {
        $rules = [
            'detail' => 'required|array|min:1',
            'detail.*' => 'required|string|min:2|max:200',
            'amount' => 'required|array|min:1',
            'amount.*' => 'required|numeric|min:0',
            'application_id' => 'nullable|array',
            'application_id.*' => 'nullable|string|max:100',
        ];

        if ($requireApplication) {
            $rules['application_id'] = 'required|array|min:1';
            $rules['application_id.*'] = 'required|string|max:100';
        }

        return $rules;
    }

    public function singleItemValidationRules(bool $requireApplication = true): array
    {
        $rules = [
            'detail' => 'required|string|min:2|max:200',
            'amount' => 'required|numeric|min:0',
            'application_id' => 'nullable|string|max:100',
        ];

        if ($requireApplication) {
            $rules['application_id'] = 'required|string|max:100';
        }

        return $rules;
    }

    public function normalizeRequestItems(Request $request): array
    {
        if (is_array($request->input('detail'))) {
            return $this->buildItemsFromArrays(
                $request->input('detail', []),
                $request->input('amount', []),
                $request->input('application_id', [])
            );
        }

        $detail = trim((string) $request->input('detail', $request->input('service_taken', '')));
        $amount = (float) $request->input('amount', 0);
        $applicationId = $request->input('application_id');

        if ($detail === '') {
            return [];
        }

        return [[
            'application_id' => $applicationId ? (string) $applicationId : null,
            'detail' => $detail,
            'amount' => $amount,
        ]];
    }

    public function buildItemsFromArrays(array $details, array $amounts, array $applicationIds = []): array
    {
        $items = [];

        foreach ($details as $index => $detail) {
            $detail = trim((string) $detail);
            if ($detail === '') {
                continue;
            }

            $items[] = [
                'application_id' => isset($applicationIds[$index]) && $applicationIds[$index] !== ''
                    ? (string) $applicationIds[$index]
                    : null,
                'detail' => $detail,
                'amount' => (float) ($amounts[$index] ?? 0),
            ];
        }

        return $items;
    }

    public function assertHasItems(array $items): void
    {
        if (count($items) < 1) {
            throw ValidationException::withMessages([
                'detail' => 'Please add at least one service line.',
            ]);
        }
    }

    public function sumAmounts(array $items): float
    {
        return round(array_sum(array_column($items, 'amount')), 2);
    }

    public function aggregateDetail(array $items): string
    {
        $details = array_values(array_filter(array_map(
            fn (array $item) => trim((string) ($item['detail'] ?? '')),
            $items
        )));

        return implode(', ', $details);
    }

    public function applyAggregatesToInvoice(Internal_Invoices $invoice, array $items): void
    {
        $invoice->detail = $this->aggregateDetail($items);
        $invoice->amount = $this->sumAmounts($items);
    }

    public function syncInvoiceItems(Internal_Invoices $invoice, array $items): Collection
    {
        InvoiceItem::where('invoice_id', $invoice->id)->delete();

        $saved = collect();

        foreach ($items as $index => $item) {
            $saved->push(InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'application_id' => $item['application_id'] ?? null,
                'detail' => $item['detail'],
                'amount' => $item['amount'],
                'sort_order' => $index,
            ]));
        }

        return $saved;
    }

    public function displayItems(Internal_Invoices $invoice): Collection
    {
        $items = $invoice->relationLoaded('items')
            ? $invoice->items
            : $invoice->items()->orderBy('sort_order')->get();

        if ($items->isNotEmpty()) {
            return $items;
        }

        if (!empty($invoice->detail)) {
            return collect([
                (object) [
                    'detail' => $invoice->detail,
                    'amount' => (float) $invoice->amount,
                    'application_id' => null,
                ],
            ]);
        }

        return collect();
    }

    public function itemsForMail(Internal_Invoices $invoice): array
    {
        return $this->displayItems($invoice)->map(function ($item) {
            return [
                'detail' => $item->detail,
                'amount' => (float) $item->amount,
                'application_id' => $item->application_id ?? null,
            ];
        })->all();
    }

    public function rowsForForm(Internal_Invoices $invoice = null): array
    {
        if (old('detail') !== null && !is_array(old('detail'))) {
            return [[
                'application_id' => old('application_id'),
                'detail' => old('detail'),
                'amount' => old('amount'),
            ]];
        }

        if (is_array(old('detail'))) {
            return $this->buildItemsFromArrays(
                old('detail', []),
                old('amount', []),
                old('application_id', [])
            );
        }

        if ($invoice) {
            $items = $this->displayItems($invoice);

            if ($items->isNotEmpty()) {
                return $items->map(fn ($item) => [
                    'application_id' => $item->application_id ?? null,
                    'detail' => $item->detail,
                    'amount' => (float) $item->amount,
                ])->all();
            }
        }

        return [[
            'application_id' => null,
            'detail' => '',
            'amount' => '',
        ]];
    }

    /**
     * Application record ids already present on non-cancelled AR invoices for a client.
     *
     * @return list<int>
     */
    public function invoicedApplicationRecordIdsForClient(int $subscriberId, int $clientId, ?int $ignoreInvoiceId = null): array
    {
        $client = Clients::find($clientId);
        if (!$client) {
            return [];
        }

        $query = InvoiceItem::query()
            ->select('invoice_items.application_id')
            ->join('internal_invoices', 'internal_invoices.id', '=', 'invoice_items.invoice_id')
            ->where('internal_invoices.subscriber_id', $subscriberId)
            ->where('internal_invoices.type', 'ar')
            ->where('internal_invoices.status', '!=', 'Cancelled')
            ->where(function ($builder) use ($client) {
                $builder->where('internal_invoices.to_email', $client->email);
                if (!empty($client->name)) {
                    $builder->orWhere('internal_invoices.to_name', $client->name);
                }
            })
            ->whereNotNull('invoice_items.application_id')
            ->where('invoice_items.application_id', '!=', '');

        if ($ignoreInvoiceId) {
            $query->where('internal_invoices.id', '!=', $ignoreInvoiceId);
        }

        $storedValues = $query->pluck('application_id')->unique()->filter()->values()->all();
        if ($storedValues === []) {
            return [];
        }

        $excludeIds = [];
        $clientApps = Applications::where('client_id', $clientId)->get(['id', 'application_id']);

        foreach ($clientApps as $app) {
            foreach ($storedValues as $stored) {
                if ((string) $stored === (string) $app->application_id || (string) $stored === (string) $app->id) {
                    $excludeIds[] = (int) $app->id;
                    break;
                }
            }
        }

        return array_values(array_unique($excludeIds));
    }

    public function clientHasUninvoicedApplications(int $subscriberId, int $clientId): bool
    {
        $applicationIds = Applications::where('client_id', $clientId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($applicationIds === []) {
            return true;
        }

        $invoicedIds = $this->invoicedApplicationRecordIdsForClient($subscriberId, $clientId);

        return count(array_diff($applicationIds, $invoicedIds)) > 0;
    }

    public function hasActiveClientApplicationInvoice(
        int $subscriberId,
        int $clientId,
        string $applicationStoredValue,
        ?int $ignoreInvoiceId = null
    ): bool {
        $stored = trim($applicationStoredValue);
        if ($stored === '' || strcasecmp($stored, 'Other') === 0) {
            return false;
        }

        $client = Clients::find($clientId);
        if (!$client) {
            return false;
        }

        $query = InvoiceItem::query()
            ->join('internal_invoices', 'internal_invoices.id', '=', 'invoice_items.invoice_id')
            ->where('internal_invoices.subscriber_id', $subscriberId)
            ->where('internal_invoices.type', 'ar')
            ->where('internal_invoices.status', '!=', 'Cancelled')
            ->where(function ($builder) use ($client) {
                $builder->where('internal_invoices.to_email', $client->email);
                if (!empty($client->name)) {
                    $builder->orWhere('internal_invoices.to_name', $client->name);
                }
            })
            ->whereNotNull('invoice_items.application_id')
            ->where('invoice_items.application_id', '!=', '')
            ->where('invoice_items.application_id', $stored);

        if ($ignoreInvoiceId) {
            $query->where('internal_invoices.id', '!=', $ignoreInvoiceId);
        }

        if ($query->exists()) {
            return true;
        }

        $invoicedIds = array_flip($this->invoicedApplicationRecordIdsForClient($subscriberId, $clientId, $ignoreInvoiceId));
        if ($invoicedIds === []) {
            return false;
        }

        foreach (Applications::where('client_id', $clientId)->get(['id', 'application_id']) as $app) {
            if (!isset($invoicedIds[$app->id])) {
                continue;
            }

            if ($stored === (string) $app->application_id || $stored === (string) $app->id) {
                return true;
            }
        }

        return false;
    }

    public function hasActiveAssociateClientApplicationInvoice(
        int $subscriberId,
        int $clientId,
        int $applicationId,
        ?int $ignoreInvoiceId = null
    ): bool {
        $query = AssociateInvoice::query()
            ->where('subscriber_id', $subscriberId)
            ->where('client_id', $clientId)
            ->where('application_id', $applicationId)
            ->where('status', '!=', 'Cancelled');

        if ($ignoreInvoiceId) {
            $query->where('id', '!=', $ignoreInvoiceId);
        }

        return $query->exists();
    }

    /**
     * @throws ValidationException
     */
    public function assertClientApplicationsAvailable(int $subscriberId, int $clientId, array $items, ?int $ignoreInvoiceId = null): void
    {
        foreach ($items as $index => $item) {
            $stored = trim((string) ($item['application_id'] ?? ''));
            if ($stored === '' || strcasecmp($stored, 'Other') === 0) {
                continue;
            }

            if ($this->hasActiveClientApplicationInvoice($subscriberId, $clientId, $stored, $ignoreInvoiceId)) {
                throw ValidationException::withMessages([
                    'application_id.' . $index => 'This application already has an active invoice. Cancel the existing invoice to invoice it again.',
                ]);
            }
        }
    }
}
