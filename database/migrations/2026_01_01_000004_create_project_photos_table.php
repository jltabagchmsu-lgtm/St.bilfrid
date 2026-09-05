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
        Schema::create('project_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->enum('photo_type', [
                'blueprint',          // Technical Architectural / Structural / MEP Blueprints & CAD
                '3d_render',          // 3D Architectural Concept Renders / "What they want / client design"
                'actual_site',        // Actual on-site construction progress
                'client_want',        // Client inspiration / design requirements
                'structural',         // Foundation, rebar, structural framing
                'finishing',          // Interior, exterior, turnkey finishes
            ])->default('actual_site');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->boolean('is_primary')->default(false);
            $table->date('taken_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_photos');
    }
};
