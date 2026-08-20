<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        if (!Schema::hasColumn('services', 'country')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('country', 100)->default('NA')->after('subscriber_id');
            });
        }

        DB::table('services')
            ->orderBy('id')
            ->chunkById(100, function ($services) {
                foreach ($services as $service) {
                    $country = trim((string) ($service->country ?? ''));
                    $name = trim((string) ($service->service_name ?? ''));

                    if ($country !== '' && strcasecmp($country, 'NA') !== 0) {
                        continue;
                    }

                    if (str_contains($name, ' - ')) {
                        [$parsedCountry, $parsedName] = explode(' - ', $name, 2);
                        $parsedCountry = trim($parsedCountry);
                        $parsedName = trim($parsedName);

                        if ($parsedCountry !== '' && $parsedName !== '') {
                            DB::table('services')
                                ->where('id', $service->id)
                                ->update([
                                    'country' => $parsedCountry,
                                    'service_name' => $parsedName,
                                ]);
                            continue;
                        }
                    }

                    if ($country === '') {
                        DB::table('services')
                            ->where('id', $service->id)
                            ->update(['country' => 'NA']);
                    }
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'country')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
