<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Safely handle existing duplicates before enforcing unique constraint
        $duplicates = DB::table('payments')
            ->select('official_receipt_no')
            ->whereNotNull('official_receipt_no')
            ->where('official_receipt_no', '!=', '')
            ->groupBy('official_receipt_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('official_receipt_no');

        foreach ($duplicates as $duplicateOr) {
            $records = DB::table('payments')
                ->where('official_receipt_no', $duplicateOr)
                ->orderBy('id')
                ->get();

            // Preserve first entry intact, disambiguate subsequent entries with suffix
            foreach ($records->slice(1) as $record) {
                DB::table('payments')
                    ->where('id', $record->id)
                    ->update([
                        'official_receipt_no' => $duplicateOr . '-DUP-' . $record->id,
                    ]);
            }
        }

        // 2. Add database unique constraint on official_receipt_no
        Schema::table('payments', function (Blueprint $table) {
            $table->unique('official_receipt_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['official_receipt_no']);
        });
    }
};
