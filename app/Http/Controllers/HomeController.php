<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the application dashboard landing page.
     */
    public function index()
    {
        $dbConnected = false;
        $dbDriver = config('database.default');
        
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'db_connection' => $dbDriver,
            'db_status' => $dbConnected ? 'Connected' : 'Pending Migration',
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
        ];

        return view('welcome', compact('systemInfo'));
    }

    /**
     * Return JSON health status endpoint.
     */
    public function status()
    {
        return response()->json([
            'status' => 'healthy',
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
