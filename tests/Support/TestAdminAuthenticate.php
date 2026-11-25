<?php

namespace Platform\Portal\Tests\Support;

use Closure;
use Illuminate\Http\Request;

class TestAdminAuthenticate
{
    /**
     * Handle an incoming request for the testbench admin middleware alias.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('admin.session.create');
        }

        return $next($request);
    }
}
