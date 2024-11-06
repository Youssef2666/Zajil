<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVerifiedPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user->phones()->exists()) {
            return response()->json(['message' => 'يجب تسجيل رقم جوال'], 403);
        }

        if (!$user->phones()->where('is_verified', true)->exists()) {
            return response()->json(['message' => 'يجب التحقق من رقم جوال'], 403);
        }
        return $next($request);
    }
}
