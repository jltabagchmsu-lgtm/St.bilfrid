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
        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('cost_code')->unique();
            $table->string('cost_category')->comment('Materials & Consumables, Labor & Engineering, Equipment & Heavy Machinery, Subcontractor & Trade, Permits & Regulatory, Site Overhead & Utilities, Contingency & Testing');
            $table->string('item_name');
            $table->string('cost_type')->default('Direct')->comment('Direct, Indirect, Subcontract, Overhead, Contingency');
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit')->default('lot')->comment('lot, sq.m, hours, days, units, trips, months, cu.m, tons, bags');
            $table->decimal('unit_rate', 12, 2)->default(0.00);
            $table->decimal('estimated_cost', 12, 2)->default(0.00);
            $table->decimal('actual_cost', 12, 2)->default(0.00);
            $table->enum('status', ['budgeted', 'committed', 'incurred', 'settled'])->default('incurred');
            $table->date('cost_date');
            $table->string('vendor_payee')->nullable()->comment('Supplier, contractor, agency or workforce payee');
            $table->string('reference_no')->nullable()->comment('PO / Invoice / Voucher / OR ref');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_costs');
    }
};
