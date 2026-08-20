<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NAME_MAP = [
        'Client account entry added' => 'Client account record added',
        'Client account entry updated' => 'Client account record updated',
        'Client account entry deleted' => 'Client account record deleted',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        foreach (self::NAME_MAP as $oldName => $newName) {
            DB::table('activities')
                ->where('activity_name', $oldName)
                ->update(['activity_name' => $newName]);
        }

        DB::table('activities')
            ->where('activity_name', 'Client account record added')
            ->where('activity_detail', 'like', '% entry added by %')
            ->update([
                'activity_detail' => DB::raw("REPLACE(activity_detail, ' entry added by ', ' record added by ')"),
            ]);

        DB::table('activities')
            ->whereIn('activity_name', ['Client account record updated', 'Client account record deleted'])
            ->where('activity_detail', 'like', '%account entry #%')
            ->update([
                'activity_detail' => DB::raw("REPLACE(activity_detail, 'account entry #', 'account record #')"),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        foreach (self::NAME_MAP as $oldName => $newName) {
            DB::table('activities')
                ->where('activity_name', $newName)
                ->update(['activity_name' => $oldName]);
        }

        DB::table('activities')
            ->where('activity_name', 'Client account entry added')
            ->where('activity_detail', 'like', '% record added by %')
            ->update([
                'activity_detail' => DB::raw("REPLACE(activity_detail, ' record added by ', ' entry added by ')"),
            ]);

        DB::table('activities')
            ->whereIn('activity_name', ['Client account entry updated', 'Client account entry deleted'])
            ->where('activity_detail', 'like', '%account record #%')
            ->update([
                'activity_detail' => DB::raw("REPLACE(activity_detail, 'account record #', 'account entry #')"),
            ]);
    }
};
