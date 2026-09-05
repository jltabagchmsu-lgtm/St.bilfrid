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
            // Workforce / Personnel On-Site Deployment Headcounts
            $table->integer('deployed_workers')->default(0)->comment('General Construction Workers / Laborers');
            $table->integer('deployed_skilled_workers')->default(0)->comment('Skilled Trades: Masons, Carpenters, Welders, Steelmen');
            $table->integer('deployed_engineers')->default(0)->comment('Site, Structural, Electrical & Piping Engineers');
            $table->integer('deployed_architects')->default(0)->comment('Architects & Design Planners');
            $table->integer('deployed_foremen')->default(0)->comment('Site Foremen & Trade Supervisors');
            $table->integer('deployed_operators')->default(0)->comment('Heavy Equipment, Crane & Excavator Operators');
            $table->integer('deployed_safety_officers')->default(0)->comment('Safety, Environmental & QA/QC Officers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'deployed_workers',
                'deployed_skilled_workers',
                'deployed_engineers',
                'deployed_architects',
                'deployed_foremen',
                'deployed_operators',
                'deployed_safety_officers',
            ]);
        });
    }
};
