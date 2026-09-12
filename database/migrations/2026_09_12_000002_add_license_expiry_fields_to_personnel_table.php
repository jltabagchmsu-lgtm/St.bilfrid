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
        Schema::table('personnel', function (Blueprint $table) {
            $table->date('license_expiry_date')->nullable()->after('license_no')->comment('PRC / Professional license validity date');
            $table->string('license_status')->default('active')->after('license_expiry_date')->comment('active, expired, inactive, suspended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropColumn(['license_expiry_date', 'license_status']);
        });
    }
};
