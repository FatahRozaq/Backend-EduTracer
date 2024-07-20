<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SetTimezone
{
    public function handle(Request $request, Closure $next)
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Jakarta');
        $request->merge(['current_time' => $now]);
        return $next($request);
    }
}
