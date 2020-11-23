<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DecodeUrls
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {   
        $routeParameters = $request->route(null)[2]; 

        foreach ($routeParameters as $key=>$routeParameter) {
            $routeParameters[$key] = urldecode($routeParameter);
        }

        $routeArray = $request->route();
        $routeArray[2] = $routeParameters;
        $request->setRouteResolver(function() use ($routeArray)
        {
            return $routeArray;
        });

        return $next($request);
    }

}