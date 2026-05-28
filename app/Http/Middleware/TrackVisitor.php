<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            \Illuminate\Support\Facades\DB::table('visitors')->insertOrIgnore([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'visit_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Ignore DB issues during migration/setup
        }

        return $next($request);
    }
}
