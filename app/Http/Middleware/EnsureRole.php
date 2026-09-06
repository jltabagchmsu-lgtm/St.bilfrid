<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role ?? 'admin';

        // Check if user's role is in the allowed list for this route
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If user is Master Admin, allow only when 'admin' is in allowed roles or when roles list is empty
        if ($userRole === 'admin') {
            if (in_array('admin', $roles) || empty($roles)) {
                return $next($request);
            }
            // Admin attempted an officer-only mutation action
            return redirect()->back()
                ->with('error', 'Action Blocked: Administrator account has View-Only auditing permissions for this trade portal. Material movements and restocking are strictly performed by the dedicated Transfer Officer.');
        }

        // If not authorized officer or supplier, redirect to their own portal with notice
        if ($userRole === 'roofing_transfer') {
            return redirect()->route('roofing.index')
                ->with('error', 'Notice: Your account is dedicated solely to Roofing Materials Transfer operations.');
        }

        if ($userRole === 'windows_doors_transfer') {
            return redirect()->route('windowsDoors.index')
                ->with('error', 'Notice: Your account is dedicated solely to Windows & Doors Materials Transfer operations.');
        }

        if ($userRole === 'supplier' || $user->isSupplier()) {
            return redirect()->route('supplier.dashboard')
                ->with('error', 'Notice: Access restricted to your dedicated Supplier Operations Dashboard.');
        }

        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }
}
