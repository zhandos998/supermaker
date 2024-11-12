<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function handle($request, Closure $next, $role)
    {
        if (count(explode('|', $role))>1) {
            $roles = explode('|', $role);
            $b = false;
            foreach ($roles as $role) {
                $b=$b||Auth::user()->hasRole($role);
                if ($b) break;
            }
            // print($b);

            if(!$b) {
                abort(404);
            }
        }
        else if(!Auth::user()->hasRole($role)) {
            abort(404);
        }
        return $next($request);
    }
}
