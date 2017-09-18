<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\DB;
use Closure;

class BeforeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        //$request = $next($request);
        $request->headers->set('Content-Type', 'text/html; charset=UTF-8');        
        
        DB::enableQueryLog();  
        return $next($request);
    }
}
