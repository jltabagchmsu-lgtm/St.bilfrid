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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'client_budget')) {
                $table->decimal('client_budget', 14, 2)->nullable()->after('contract_budget')->comment('Stated Client Budget / Investment Target Cap');
            }
            if (!Schema::hasColumn('projects', 'estimated_cost')) {
                $table->decimal('estimated_cost', 14, 2)->nullable()->after('client_budget')->comment('Benchmark Automated Calculated Construction Cost');
            }
            if (!Schema::hasColumn('projects', 'finish_tier')) {
                $table->string('finish_tier')->default('standard')->after('project_type')->comment('standard, executive, luxury');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['client_budget', 'estimated_cost', 'finish_tier']);
        });
    }
};
