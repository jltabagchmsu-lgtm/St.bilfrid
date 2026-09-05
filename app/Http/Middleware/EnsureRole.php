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

        // Master admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user's role is in the allowed list
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If not authorized, redirect to their dedicated portal with a notice
        if ($userRole === 'roofing_transfer') {
            return redirect()->route('roofing.index')
                ->with('error', 'Notice: Your account is dedicated solely to Roofing Materials Transfer operations.');
        }

        if ($userRole === 'windows_doors_transfer') {
            return redirect()->route('windowsDoors.index')
                ->with('error', 'Notice: Your account is dedicated solely to Windows & Doors Materials Transfer operations.');
        }

        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }
}
