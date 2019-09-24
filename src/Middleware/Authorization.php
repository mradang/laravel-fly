<?php

namespace mradang\LaravelFly\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Authorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            return response('Unauthorized', 401);
        }

        $user = Auth::guard($guard)->user();
        $access = $user->access;
        $path = $request->getPathInfo();

        if (!Str::startsWith($path, '/api/')) {
            return response('Forbidden', 403);
        }

        if (!in_array(Str::after($path, '/api'), $access)) {
            return response('Forbidden', 403);
        }

        return $next($request);
    }
}
