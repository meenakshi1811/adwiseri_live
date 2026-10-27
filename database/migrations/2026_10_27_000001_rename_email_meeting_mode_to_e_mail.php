<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Meeting Notes mode "Email" is now spelled "E-mail" (like "E-meet"); rename existing notes so the
     * filters, reports and charts don't show both spellings.
     */
    public function up(): void
    {
        if (!Schema::hasTable('client_discussions') || !Schema::hasColumn('client_discussions', 'communication_type')) {
            return;
        }

        $this->allowAnyMode();

        DB::table('client_discussions')
            ->where('communication_type', 'Email')
            ->update(['communication_type' => 'E-mail']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('client_discussions') || !Schema::hasColumn('client_discussions', 'communication_type')) {
            return;
        }

        DB::table('client_discussions')
            ->where('communication_type', 'E-mail')
            ->update(['communication_type' => 'Email']);
    }

    /**
     * The column was added outside migrations. If it is an ENUM of the old modes, widen it to text so
     * "E-mail" can be stored; a text column is left as it is.
     */
    private function allowAnyMode(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $column = DB::selectOne(
            'SELECT DATA_TYPE AS data_type, IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default'
            . ' FROM information_schema.COLUMNS'
            . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'client_discussions' AND COLUMN_NAME = 'communication_type'"
        );

        if (!$column || strtolower((string) $column->data_type) !== 'enum') {
            return;
        }

        $definition = 'VARCHAR(50) ' . ($column->is_nullable === 'YES' ? 'NULL' : 'NOT NULL');
        if ($column->column_default !== null) {
            $definition .= ' DEFAULT ' . DB::getPdo()->quote((string) $column->column_default);
        }

        DB::statement('ALTER TABLE client_discussions MODIFY communication_type ' . $definition);
    }
};
