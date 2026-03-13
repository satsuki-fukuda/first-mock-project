<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProfileSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
        return $next($request);
        }

        $user = Auth::user();
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()) {
            if ($request->routeIs('verification.*')) {
                return $next($request);
            }
            return redirect()->route('verification.notice');
        }

        if (empty($user->address)) {
            if ($request->routeIs('profile.edit') || $request->routeIs('profile.update')) {
                return $next($request);
            }

            return redirect()->route('profile.edit')
                ->with('info', '最初にプロフィールを設定してください');
        }
        return $next($request);
    }
}
