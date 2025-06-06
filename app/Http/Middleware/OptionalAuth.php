<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {

        // Пробуем авторизовать, если есть токен
        if ($request->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer ' . $request->bearerToken());
            auth('sanctum')->user(); // вручную триггерим auth
        }

        return $next($request);
    }
}
