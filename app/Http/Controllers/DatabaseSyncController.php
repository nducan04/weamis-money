<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

class DatabaseSyncController extends Controller
{
    public function export(Request $request)
    {
        $syncKey = env('DB_SYNC_KEY', 'weamis_money_secret_sync_key_2026');
        $inputKey = $request->query('key');

        if (!$inputKey || !hash_equals($syncKey, $inputKey)) {
            if (!auth()->user()?->isAdmin()) {
                return response()->json(['error' => 'Unauthorized: Invalid sync key.'], 403);
            }
        }

        return response()->json([
            'funds' => Schema::hasTable('funds') ? Fund::all() : [],
            'users' => Schema::hasTable('users') ? User::all() : [],
            'projects' => Schema::hasTable('projects') ? Project::all() : [],
            'project_user' => Schema::hasTable('project_user') ? DB::table('project_user')->get() : [],
            'transactions' => Schema::hasTable('transactions') ? Transaction::all() : [],
            'distributions' => Schema::hasTable('distributions') ? Distribution::all() : [],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
