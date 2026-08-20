<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyName(string $table, string $column): ?string
    {
        $database = Schema::getConnection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$database, $table, $column]
        );

        return $row->name ?? null;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT INDEX_NAME AS name
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $indexName]
        );

        return $row !== null;
    }

    private function replaceUserIdUniqueWithComposite(): void
    {
        $table = 'payment_reminder_settings';

        if ($this->indexExists($table, 'payment_reminder_settings_user_type_unique')) {
            return;
        }

        $foreignKey = $this->foreignKeyName($table, 'user_id');

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
            if ($foreignKey !== null) {
                $blueprint->dropForeign($foreignKey);
            }
        });

        Schema::table($table, function (Blueprint $blueprint) {
            if ($this->indexExists('payment_reminder_settings', 'payment_reminder_settings_user_id_unique')) {
                $blueprint->dropUnique('payment_reminder_settings_user_id_unique');
            } elseif ($this->indexExists('payment_reminder_settings', 'user_id')) {
                $blueprint->dropUnique(['user_id']);
            }

            $blueprint->unique(['user_id', 'reminder_type'], 'payment_reminder_settings_user_type_unique');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function up(): void
    {
        if (Schema::hasTable('payment_reminder_settings')) {
            if (!Schema::hasColumn('payment_reminder_settings', 'reminder_type')) {
                Schema::table('payment_reminder_settings', function (Blueprint $table) {
                    $table->string('reminder_type', 20)->default('payments')->after('user_id');
                });

                DB::table('payment_reminder_settings')->update(['reminder_type' => 'payments']);
            }

            $this->replaceUserIdUniqueWithComposite();
        }

        if (!Schema::hasTable('application_reminders')) {
            Schema::create('application_reminders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('client_id');
                $table->unsignedBigInteger('application_id');
                $table->string('subject');
                $table->text('description')->nullable();
                $table->date('deadline');
                $table->enum('email_frequency', ['daily', 'weekly', 'monthly', 'quarterly'])->default('weekly');
                $table->enum('email_to', ['user_only', 'user_bcc_subscriber'])->default('user_only');
                $table->unsignedBigInteger('notify_user_id')->nullable();
                $table->timestamp('last_sent_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
                $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
                $table->foreign('notify_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_reminders');

        if (!Schema::hasTable('payment_reminder_settings') || !Schema::hasColumn('payment_reminder_settings', 'reminder_type')) {
            return;
        }

        $table = 'payment_reminder_settings';
        $foreignKey = $this->foreignKeyName($table, 'user_id');

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
            if ($foreignKey !== null) {
                $blueprint->dropForeign($foreignKey);
            }
        });

        Schema::table($table, function (Blueprint $blueprint) {
            if ($this->indexExists('payment_reminder_settings', 'payment_reminder_settings_user_type_unique')) {
                $blueprint->dropUnique('payment_reminder_settings_user_type_unique');
            }

            $blueprint->unique('user_id', 'payment_reminder_settings_user_id_unique');
            $blueprint->dropColumn('reminder_type');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
