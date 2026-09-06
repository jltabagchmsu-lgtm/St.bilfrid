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
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'is_new_product')) {
                $table->boolean('is_new_product')->default(false)->after('stock_quantity');
            }
            if (!Schema::hasColumn('materials', 'last_purchased_at')) {
                $table->timestamp('last_purchased_at')->nullable()->after('is_new_product');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'is_new_product')) {
                $table->dropColumn('is_new_product');
            }
            if (Schema::hasColumn('materials', 'last_purchased_at')) {
                $table->dropColumn('last_purchased_at');
            }
        });
    }
};
