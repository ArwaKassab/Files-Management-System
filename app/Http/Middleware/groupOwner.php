<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class groupOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $group = Group::query()->find($request->route()->id);
//        $group = Group::find($request->group_id);
        $user= auth()->user();

        if($group->owner != $user->id) {
            return response()->json(['You dont have access'], 401);
        }

        return $next($request);
    }
}
