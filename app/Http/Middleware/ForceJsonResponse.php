<?php

namespace App\Http\Middleware;

class ForceJsonResponse
{
    public function handle($request, \Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}