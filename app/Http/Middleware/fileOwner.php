<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class fileOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $file = File::query()->find($request->route()->id);
        if(!$file)
        {
            return response()->json('Invalid id', 401);
        }

        $user= auth()->user();
        if($user->id == $file->user_id)
        {
            return $next($request);
        }
        else return response()->json('Invalid Access', 401);

    }
}
