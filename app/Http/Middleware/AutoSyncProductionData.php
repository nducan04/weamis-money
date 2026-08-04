<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Project;

class AutoSyncProductionData
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
