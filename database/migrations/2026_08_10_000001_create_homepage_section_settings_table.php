<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('homepage_section_settings')) {
            Schema::create('homepage_section_settings', function (Blueprint $table) {
                $table->id();
                $table->json('sections')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('homepage_section_settings') && DB::table('homepage_section_settings')->count() === 0) {
            DB::table('homepage_section_settings')->insert([
                'sections' => json_encode(\App\Models\HomepageSectionSetting::defaultVisibility()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_section_settings');
    }
};
