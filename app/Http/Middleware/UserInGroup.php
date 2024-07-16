<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Group;
use App\Models\Group_user;

class UserInGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request has the 'id' parameter
        if (!$request->route('id')) {
            return response()->json(['No group ID provided'], 401);
        }

        // Retrieve the group based on the provided ID
        $group = Group::find($request->id);
        if (!$group) {
            return response()->json('Group is not found', 401);
        }

        // Retrieve the authenticated user
        $user = auth()->user();

        // Check if the user is a member of the group
        $search = Group_user::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$search) {
            return response()->json(['The user is not in this group'], 403);
        }

        return $next($request);
    }
}
