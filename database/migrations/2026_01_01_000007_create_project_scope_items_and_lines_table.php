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
        // 1. High-Level Scope of Work Items (e.g. ITEM 1. FOUNDATION AND FOOTING, ITEM 2. COLUMNS, etc.)
        Schema::create('project_scope_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->integer('item_number')->default(1);
            $table->string('item_name'); // e.g. "FOUNDATION AND FOOTING", "COLUMNS", "BEAMS", "ROOFING"
            $table->string('volume_or_area')->nullable(); // e.g. "Volume of Concrete : 2.61 cu.m", "Area : 79.24 Sq.m"
            $table->text('notes')->nullable(); // e.g. "4 units C1, 5 units C2, 3 units C3 & 2 units C4"
            
            // Subtotals
            $table->decimal('materials_subtotal', 14, 2)->default(0.00);
            $table->decimal('labor_subtotal', 14, 2)->default(0.00);
            $table->decimal('equipment_subtotal', 14, 2)->default(0.00);
            $table->decimal('direct_cost', 14, 2)->default(0.00);
            
            // Markups & Additions
            $table->decimal('contingency_percent', 5, 2)->default(15.00); // 15%
            $table->decimal('contingency_amount', 14, 2)->default(0.00);
            $table->decimal('taxes_percent', 5, 2)->default(6.00);        // 6%
            $table->decimal('taxes_amount', 14, 2)->default(0.00);
            $table->decimal('profit_percent', 5, 2)->default(10.00);       // 10%
            $table->decimal('profit_amount', 14, 2)->default(0.00);
            
            // Total Item Cost
            $table->decimal('total_item_cost', 14, 2)->default(0.00);
            
            $table->timestamps();
        });

        // 2. Itemized Detailed Unit Price Analysis (DUPA) Lines (Materials, Labor, Equipment)
        Schema::create('project_scope_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_scope_item_id')->constrained('project_scope_items')->onDelete('cascade');
            $table->string('category')->default('material'); // 'material', 'labor', 'equipment'
            $table->string('description'); // e.g. "16mmx6m Corr. Steel bar", "Excavation", "10% of Materials"
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->string('unit')->default('pcs'); // 'lghts', 'kls', 'pcs', 'cu.m', 'bags', 'sheets', 'rolls', 'sets', 'Lump Sum', 'sq.m'
            $table->decimal('unit_price', 14, 2)->default(0.00);
            $table->decimal('total_cost', 14, 2)->default(0.00);
            
            // Tracking & Inventory integration
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->decimal('used_quantity', 12, 2)->default(0.00);
            $table->decimal('excess_returned_quantity', 12, 2)->default(0.00);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_scope_lines');
        Schema::dropIfExists('project_scope_items');
    }
};
