<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MAX_ITEM_AMOUNT = 9999999999.99;

    public function up(): void
    {
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('application_id', 100)->nullable();
                $table->string('detail', 200);
                $table->decimal('amount', 12, 2)->default(0);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('invoice_id')
                    ->references('id')
                    ->on('internal_invoices')
                    ->onDelete('cascade');

                $table->index(['invoice_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('internal_invoices')) {
            return;
        }

        $this->repairLegacyInvoiceAmounts();

        $now = now();
        $existingInvoiceIds = DB::table('invoice_items')->pluck('invoice_id')->all();

        $invoices = DB::table('internal_invoices')
            ->whereNotNull('detail')
            ->where('detail', '!=', '')
            ->when(!empty($existingInvoiceIds), function ($query) use ($existingInvoiceIds) {
                $query->whereNotIn('id', $existingInvoiceIds);
            })
            ->get(['id', 'detail', 'amount']);

        foreach ($invoices as $invoice) {
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoice->id,
                'application_id' => null,
                'detail' => mb_substr((string) $invoice->detail, 0, 200),
                'amount' => $this->sanitizeAmount($invoice->amount),
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }

    private function repairLegacyInvoiceAmounts(): void
    {
        DB::table('internal_invoices')
            ->select(['id', 'amount'])
            ->orderBy('id')
            ->chunkById(200, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $sanitized = $this->sanitizeAmount($invoice->amount);
                    $raw = $invoice->amount;

                    if ($this->amountNeedsRepair($raw, $sanitized)) {
                        DB::table('internal_invoices')
                            ->where('id', $invoice->id)
                            ->update(['amount' => $sanitized]);
                    }
                }
            });
    }

    private function amountNeedsRepair($raw, string $sanitized): bool
    {
        if ($raw === null || $raw === '') {
            return false;
        }

        if (is_string($raw) && stripos($raw, 'e') !== false) {
            return true;
        }

        $numeric = (float) $raw;

        return !is_finite($numeric)
            || $numeric < 0
            || $numeric > self::MAX_ITEM_AMOUNT
            || number_format(round($numeric, 2), 2, '.', '') !== $sanitized;
    }

    private function sanitizeAmount($raw): string
    {
        if ($raw === null || $raw === '') {
            return '0.00';
        }

        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '' || !is_numeric($trimmed)) {
                return '0.00';
            }
            $raw = $trimmed;
        }

        $amount = (float) $raw;

        if (!is_finite($amount) || $amount < 0) {
            return '0.00';
        }

        if ($amount > self::MAX_ITEM_AMOUNT) {
            $amount = self::MAX_ITEM_AMOUNT;
        }

        return number_format(round($amount, 2), 2, '.', '');
    }
};
