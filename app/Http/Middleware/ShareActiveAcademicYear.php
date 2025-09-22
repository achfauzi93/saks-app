<?php

namespace App\Http\Middleware;

use Closure;
use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareActiveAcademicYear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        Inertia::share('activeAcademicYear', $activeYear);

        return $next($request);
    }
}