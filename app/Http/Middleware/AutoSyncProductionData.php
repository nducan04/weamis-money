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
        // Only run auto-sync in local development environment
        if (app()->environment('local')) {
            $hasNoProjects = Project::count() === 0;
            
            // Sync if DB is empty or if 60 seconds have passed since last auto-sync
            if ($hasNoProjects || !Cache::has('auto_db_sync_cooldown')) {
                Cache::put('auto_db_sync_cooldown', true, 60); // 60 seconds cooldown
                try {
                    Artisan::call('db:sync');
                } catch (\Throwable $e) {
                    // Silent fail if production server is unreachable
                }
            }
        }

        return $next($request);
    }
}
