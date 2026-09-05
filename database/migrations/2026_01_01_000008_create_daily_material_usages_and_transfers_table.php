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
        // 1. Daily Material Usage Journal (Records daily on-site consumption)
        Schema::create('daily_material_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('project_material_id')->constrained('project_materials')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->date('usage_date');
            $table->decimal('quantity_used', 12, 2)->default(0.00);
            $table->string('activity_description'); // e.g. "Level 9 Floor Slab Pouring", "CHB Perimeter Wall Laying"
            $table->string('logged_by')->default('Site Project Engineer');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Inter-Project Material Transfers (Transfer remaining surplus to another project or central inventory)
        Schema::create('project_material_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('destination_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->decimal('quantity_transferred', 12, 2)->default(0.00);
            $table->date('transfer_date');
            $table->string('transfer_reference_no'); // e.g. "XFER-202608-001"
            $table->string('transfer_type')->default('inter_project'); // 'inter_project', 'warehouse_stock'
            $table->string('reason')->nullable(); // e.g. "Transferred surplus rebar to Villa build"
            $table->string('authorized_by')->default('Engr. Sophia Martinez, PMP');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_material_transfers');
        Schema::dropIfExists('daily_material_usages');
    }
};
