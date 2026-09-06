<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('supplier_orders', 'is_synced_to_inventory')) {
            Schema::table('supplier_orders', function (Blueprint $table) {
                $table->boolean('is_synced_to_inventory')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('supplier_orders', 'is_synced_to_inventory')) {
            Schema::table('supplier_orders', function (Blueprint $table) {
                $table->dropColumn('is_synced_to_inventory');
            });
        }
    }
};
