<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        // Automatically ensure supplier tables and accounts exist on live server
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('suppliers') && \App\Models\Supplier::count() === 0) {
                (new \Database\Seeders\SupplierManagementSeeder())->run();
            }
        } catch (\Throwable $e) {
            // Silently continue
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'roofing_transfer') {
                return redirect()->route('roofing.index');
            } elseif ($user->role === 'windows_doors_transfer') {
                return redirect()->route('windowsDoors.index');
            } elseif ($user->isSupplier()) {
                return redirect()->route('supplier.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle the login submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Auto-seed supplier accounts on-the-fly if missing when logging in
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                if (\App\Models\Supplier::count() === 0 || \App\Models\User::where('email', $credentials['email'])->doesntExist()) {
                    (new \Database\Seeders\SupplierManagementSeeder())->run();
                }
            }
        } catch (\Throwable $e) {
            // Silently continue
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'roofing_transfer') {
                return redirect()->route('roofing.index')
                    ->with('success', 'Welcome, ' . $user->name . '! Signed in to Roofing Materials Transfer Station.');
            } elseif ($user->role === 'windows_doors_transfer') {
                return redirect()->route('windowsDoors.index')
                    ->with('success', 'Welcome, ' . $user->name . '! Signed in to Windows & Doors Materials Transfer Station.');
            } elseif ($user->isSupplier()) {
                $supplierName = $user->supplier ? $user->supplier->name : 'Supplier Portal';
                return redirect()->route('supplier.dashboard')
                    ->with('success', 'Welcome, ' . $user->name . '! Signed in to ' . $supplierName . ' Management Dashboard.');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '! Access granted to Master Control.');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials. Please verify your department email and password.',
        ])->onlyInput('email');
    }

    /**
     * Log out the admin user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been safely logged out of the system.');
    }
}
