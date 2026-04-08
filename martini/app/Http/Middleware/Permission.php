<?php

namespace App\Http\Middleware;

use App\Models\Permission as ModelsPermission;
use Closure;
use Illuminate\Http\Request;

class Permission
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
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $routePath = $request->route()->uri();
        $p = ModelsPermission::where('file','LIKE', '%'.$routePath.'%')->first();
        if (!$p)  return $next($request);
        if ($user->hasPermission($p)) return $next($request);

        $back = url()->previous();
        if (!str_contains($back,"legacy")) return redirect()->back()->withErrors(["You don't have access to the requested page."]);
        return redirect()->route('dashboard')->withErrors(["You don't have access to the requested page."]);
    }
}
