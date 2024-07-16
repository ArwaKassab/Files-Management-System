<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class Logging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        DB::beginTransaction();

        try {
            // Proceed with the request
            $response = $next($request);
    
            // Log incoming request
            $requestLog = RequestLog::create([
                'requestMethod' => $request->method(),
                'url' => $request->fullUrl(),
                'requestData' => json_encode($request->all()), // Convert array to JSON
                'responseData' => '', // Initialize responseData with an empty string
            ]);
    
            // Log outgoing response
            $requestLog->update([
                'responseData' => $response->getContent(), // Update the responseData
            ]);


            // Commit the database transaction
            DB::commit();
    
            return $response;
        } catch (\Exception $e) {
            // An exception occurred, rollback the transaction
            DB::rollBack();
    
            // Log the exception if needed
            // Log::error($e);
    
            // Re-throw the exception to let Laravel handle it
            throw $e;
        }
    }
}
