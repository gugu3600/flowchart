<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtCookieMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($token = $request->cookie('jwt_token')) {
            $request->headers->set('Authorization', "Bearer $token");
        }

        return $next($request);
    }
}
