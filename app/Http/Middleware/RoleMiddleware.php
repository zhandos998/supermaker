<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolesAndPermissions;

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
    public function handle($request, Closure $next, $role, $guard = 'web')
    {
        if (!auth($guard)->check()) {
            return abort(403); // Пользователь не авторизован в указанном guard
        }

        $user = auth($guard)->user();

        if (str_contains($role, '|')) {
            $roles = explode('|', $role);
            $hasAny = false;

            foreach ($roles as $r) {
                if ($user->hasRole($r)) {
                    $hasAny = true;
                    break;
                }
            }

            if (!$hasAny) {
                abort(404);
            }
        } else {
            if (!$user->hasRole($role)) {
                abort(404);
            }
        }

        return $next($request);
    }


}
