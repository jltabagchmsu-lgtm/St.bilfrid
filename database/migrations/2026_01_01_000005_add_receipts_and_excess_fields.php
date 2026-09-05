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
        // 1. Add receipt verification fields to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_file')->nullable()->after('notes')->comment('Path to uploaded payment receipt/proof');
            $table->string('official_receipt_no')->nullable()->after('invoice_no')->comment('Official Receipt (OR) Number');
            $table->string('payer_name')->nullable()->after('official_receipt_no')->comment('Client / Payer Entity Name');
            $table->string('bank_reference')->nullable()->after('payment_method')->comment('Cheque / Wire / Deposit Ref');
            $table->string('received_by')->nullable()->after('bank_reference')->comment('Authorized Financial Officer');
        });

        // 2. Add excess returned quantity to project_materials (BOM)
        Schema::table('project_materials', function (Blueprint $table) {
            $table->integer('excess_returned_qty')->default(0)->after('used_qty')->comment('Unused excess materials returned to central inventory');
        });

        // 3. Add weighted progression bases & phase tracking to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('finishing_progress')->default(0)->after('piping_progress')->comment('Architectural & Turnkey Finishing %');
            $table->integer('structural_weight')->default(40)->after('finishing_progress')->comment('Weight % for Structural Works');
            $table->integer('electrical_weight')->default(25)->after('structural_weight')->comment('Weight % for Electrical Works');
            $table->integer('piping_weight')->default(20)->after('electrical_weight')->comment('Weight % for Piping/Plumbing Works');
            $table->integer('finishing_weight')->default(15)->after('piping_weight')->comment('Weight % for Finishing Works');
            $table->string('current_phase')->default('Phase 1: Mobilization & Site Prep')->after('overall_progress');
            $table->text('schedule_notes')->nullable()->after('description');
        });

        // 4. Central Inventory Movement & Excess Reconciliation Log Table
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('transaction_type')->default('usage'); // 'allocation', 'usage', 'excess_return', 'restock', 'adjustment', 'inter_project_transfer'
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'finishing_progress',
                'structural_weight',
                'electrical_weight',
                'piping_weight',
                'finishing_weight',
                'current_phase',
                'schedule_notes',
            ]);
        });

        Schema::table('project_materials', function (Blueprint $table) {
            $table->dropColumn('excess_returned_qty');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_file',
                'official_receipt_no',
                'payer_name',
                'bank_reference',
                'received_by',
            ]);
        });
    }
};
