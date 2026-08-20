<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('landing_promo_settings')) {
            Schema::create('landing_promo_settings', function (Blueprint $table) {
                $table->id();
                $table->string('heading')->default('Discounts & Offers');
                $table->string('image')->nullable();
                $table->text('discount_note')->nullable();
                $table->text('offer_note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('landing_promo_items')) {
            Schema::create('landing_promo_items', function (Blueprint $table) {
                $table->id();
                $table->string('category', 20); // discount | offer
                $table->string('benefit', 120);
                $table->string('detail', 255);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (DB::table('landing_promo_settings')->count() === 0) {
            DB::table('landing_promo_settings')->insert([
                'heading' => 'Discounts & Offers',
                'discount_note' => 'Discounts cannot be combined with any existing or newly introduced offer(s).',
                'offer_note' => 'For New Subscribers only. Cashbacks are rewarded in the form of wallet credits.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('landing_promo_items')->count() === 0) {
            $now = now();
            $rows = [
                ['category' => 'discount', 'benefit' => '10%', 'detail' => '2 years Subscription', 'sort_order' => 1],
                ['category' => 'discount', 'benefit' => '20%', 'detail' => '3 years Subscription', 'sort_order' => 2],
                ['category' => 'discount', 'benefit' => '50%', 'detail' => '5 years Subscription', 'sort_order' => 3],
                ['category' => 'offer', 'benefit' => '25% cashback', 'detail' => 'Solo', 'sort_order' => 1],
                ['category' => 'offer', 'benefit' => '50% cashback', 'detail' => 'Adwiseri', 'sort_order' => 2],
                ['category' => 'offer', 'benefit' => '75% cashback', 'detail' => 'Adwiseri+', 'sort_order' => 3],
                ['category' => 'offer', 'benefit' => '100% cashback', 'detail' => 'Enterprise', 'sort_order' => 4],
            ];

            foreach ($rows as $row) {
                DB::table('landing_promo_items')->insert(array_merge($row, [
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_promo_items');
        Schema::dropIfExists('landing_promo_settings');
    }
};
