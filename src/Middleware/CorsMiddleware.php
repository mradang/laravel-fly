<?php

namespace mradang\LaravelFly\Middleware;

use Closure;

class CorsMiddleware
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
        $allow_origin = explode('|', config('fly.cors'));
        $origin = $request->server('HTTP_ORIGIN');

        $headers = [];
        if (in_array(strtolower($origin), $allow_origin)) {
            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
                'Access-Control-Max-Age' => '86400',
                'Access-Control-Allow-Headers' => 'Content-Type, authorization'
            ];
        }

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', $origin);

        return $response;
    }
}
