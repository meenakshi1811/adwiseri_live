<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            ['w' => 520, 'h' => 360],
            ['w' => 520, 'h' => 300],
            ['w' => 458, 'h' => 458],
            ['w' => 458, 'h' => 300],
            ['w' => 458, 'h' => 229],
            ['w' => 360, 'h' => 360],
            ['w' => 320, 'h' => 300],
            ['w' => 229, 'h' => 229],
        ];

        // Prefer an existing table commonly used for ad/image sizes.
        $table = collect(['sizes', 'ad_sizes', 'banner_sizes', 'image_sizes'])
            ->first(fn (string $candidate) => Schema::hasTable($candidate));

        if (!$table) {
            return;
        }

        foreach ($sizes as $size) {
            $payload = [
                'width' => $size['w'],
                'height' => $size['h'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($table, 'user_type')) {
                $payload['user_type'] = 'admin';
            }

            if (Schema::hasColumn($table, 'audience')) {
                $payload['audience'] = 'admin';
            }

            if (Schema::hasColumn($table, 'is_admin_only')) {
                $payload['is_admin_only'] = 1;
            }

            DB::table($table)->updateOrInsert(
                ['width' => $size['w'], 'height' => $size['h']],
                $payload
            );
        }
    }
}
