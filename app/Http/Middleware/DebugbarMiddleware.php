<?php
namespace App\Http\Middleware;

use Closure;
use Barryvdh\Debugbar\Facades\Debugbar;

class DebugbarMiddleware
{
    public function handle($request, Closure $next)
    {
        
        // Check if Debugbar is enabled before adding messages
        // if (Debugbar::isEnabled()) {
        //     Debugbar::addMessage('Debugging!');
        // }

        // return $next($request);
    }
}
