<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enhance project_photos to support specific blueprint disciplines (civil, plumbing, electrical, architectural, other)
        Schema::table('project_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('project_photos', 'plan_discipline')) {
                $table->string('plan_discipline', 50)->nullable()->after('photo_type');
            }
        });

        // If MySQL, modify photo_type from ENUM to VARCHAR(50) so blueprint disciplines can be stored directly
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE project_photos MODIFY COLUMN photo_type VARCHAR(50) NOT NULL DEFAULT 'actual_site'");
            }
        } catch (\Throwable $e) {
            // Silently continue if alter fails or already modified
        }

        // 2. Create project_task_photos table to handle 2 or more images per checklist task
        if (!Schema::hasTable('project_task_photos')) {
            Schema::create('project_task_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_task_id')->constrained('project_tasks')->onDelete('cascade');
                $table->string('photo_path', 1000);
                $table->string('caption', 500)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 3. Backfill existing task photos into project_task_photos
        try {
            if (Schema::hasColumn('project_tasks', 'photo_path')) {
                $existingTasksWithPhotos = DB::table('project_tasks')
                    ->whereNotNull('photo_path')
                    ->where('photo_path', '!=', '')
                    ->select('id', 'photo_path', 'photo_caption', 'created_at', 'updated_at')
                    ->get();

                foreach ($existingTasksWithPhotos as $task) {
                    $alreadyExists = DB::table('project_task_photos')
                        ->where('project_task_id', $task->id)
                        ->where('photo_path', $task->photo_path)
                        ->exists();

                    if (!$alreadyExists) {
                        DB::table('project_task_photos')->insert([
                            'project_task_id' => $task->id,
                            'photo_path' => $task->photo_path,
                            'caption' => $task->photo_caption,
                            'sort_order' => 0,
                            'created_at' => $task->created_at ?? now(),
                            'updated_at' => $task->updated_at ?? now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if table/column does not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_task_photos');

        Schema::table('project_photos', function (Blueprint $table) {
            if (Schema::hasColumn('project_photos', 'plan_discipline')) {
                $table->dropColumn('plan_discipline');
            }
        });
    }
};
