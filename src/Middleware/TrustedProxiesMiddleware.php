<?php

namespace mradang\LaravelFly\Middleware;

use Closure;

class TrustedProxiesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $proxies = explode('|', config('fly.trusted_proxies'));
        $request::setTrustedProxies($proxies, $request::HEADER_X_FORWARDED_ALL);
        return $next($request);
    }
}
