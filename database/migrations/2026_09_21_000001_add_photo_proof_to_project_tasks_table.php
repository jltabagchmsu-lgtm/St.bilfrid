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
            if (!Schema::hasColumn('project_tasks', 'photo_path')) {
                $table->string('photo_path', 1000)->nullable()->after('timeline_month');
            }
            if (!Schema::hasColumn('project_tasks', 'photo_caption')) {
                $table->string('photo_caption', 500)->nullable()->after('photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('project_tasks', 'photo_caption')) {
                $table->dropColumn('photo_caption');
            }
            if (Schema::hasColumn('project_tasks', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
