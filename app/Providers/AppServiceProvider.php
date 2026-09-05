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
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('projects')) {
                    $navProjects = \App\Models\Project::orderBy('status', 'asc')
                        ->orderBy('title', 'asc')
                        ->get(['id', 'title', 'project_code', 'status']);
                    $view->with('navProjects', $navProjects);
                }
            } catch (\Exception $e) {
                // In case migration hasn't run yet
                $view->with('navProjects', collect());
            }
        });
    }
}
