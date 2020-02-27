<?php

namespace mradang\LaravelFly\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $guard = 'api';

        if (Auth::guard($guard)->guest()) {
            abort(401);
        }

        return $next($request);
    }
}
