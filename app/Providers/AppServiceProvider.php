<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-migrate and seed supplier tables on remote web host if they don't exist yet
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
            
            if (\Illuminate\Support\Facades\Schema::hasTable('suppliers') && \App\Models\Supplier::count() === 0) {
                (new \Database\Seeders\SupplierManagementSeeder())->run();
            }
        } catch (\Throwable $e) {
            // Silently continue if database is not yet reachable
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('projects')) {
                    $navProjects = \App\Models\Project::orderBy('status', 'asc')
                        ->orderBy('title', 'asc')
                        ->get([
                            'id', 'title', 'project_code', 'status', 'client_name', 'location',
                            'overall_progress', 'contract_budget', 'spent_budget',
                            'structural_progress', 'electrical_progress', 'piping_progress', 'finishing_progress'
                        ]);
                    $view->with('navProjects', $navProjects);
                }
            } catch (\Exception $e) {
                // In case migration hasn't run yet
                $view->with('navProjects', collect());
            }
        });
    }
}
