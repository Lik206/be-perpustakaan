<?php

namespace App\Http\Middleware;

use App\Models\Roles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $role = Roles::find($user->role_id);

        if ($role->name !== 'owner') {
            return response()->json([
                'message' => 'Halaman ini hanya untuk admin'
            ]);
        }


        return $next($request);
    }
}
