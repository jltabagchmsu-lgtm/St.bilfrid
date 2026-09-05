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
        Schema::table('project_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('project_tasks', 'timeline_phase')) {
                $table->string('timeline_phase')->nullable();
            }
            if (!Schema::hasColumn('project_tasks', 'timeline_month')) {
                $table->string('timeline_month')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('project_tasks', 'timeline_phase')) {
                $table->dropColumn(['timeline_phase', 'timeline_month']);
            }
        });
    }
};
