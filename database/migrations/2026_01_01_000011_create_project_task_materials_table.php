<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('material_name');
            $table->string('category')->default('Structural');
            $table->string('unit')->default('pcs');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, consumed, completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_materials');
    }
};
