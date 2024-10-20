<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
     /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @param $role
     * @param null $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $permission = null)
    {
        if (count(explode('|', $role))>1) {
            $roles = explode('|', $role);
            $b = false;
            foreach ($roles as $role) {
                $b=$b||auth()->user()->hasRole($role);
                if ($b) break;
            }
            // print($b);

            if(!$b) {
                abort(404);
            }
        }
        else if(!auth()->user()->hasRole($role)) {
            abort(404);
        }
        if($permission !== null && !auth()->user()->can($permission)) {
            abort(404);
        }
        return $next($request);
    }
}
