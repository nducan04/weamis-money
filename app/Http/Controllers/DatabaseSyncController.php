<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Distribution;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DatabaseSyncController extends Controller
{
    private function verifyKey(Request $request): bool
    {
        $syncKey = env('DB_SYNC_KEY', 'weamis_money_secret_sync_key_2026');
        $inputKey = $request->header('X-Sync-Key', $request->query('key', $request->input('key')));

        if ($inputKey && hash_equals($syncKey, (string)$inputKey)) {
            return true;
        }

        return auth()->user()?->isAdmin() ?? false;
    }

    public function export(Request $request)
    {
        if (!$this->verifyKey($request)) {
            return response()->json(['error' => 'Unauthorized: Invalid sync key.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'funds'           => Schema::hasTable('funds') ? DB::table('funds')->get() : [],
                'users'           => Schema::hasTable('users') ? DB::table('users')->get() : [],
                'projects'        => Schema::hasTable('projects') ? DB::table('projects')->get() : [],
                'project_members' => Schema::hasTable('project_members') ? DB::table('project_members')->get() : [],
                'transactions'    => Schema::hasTable('transactions') ? DB::table('transactions')->get() : [],
                'accounts'        => Schema::hasTable('accounts') ? DB::table('accounts')->get() : [],
                'journal_entries' => Schema::hasTable('journal_entries') ? DB::table('journal_entries')->get() : [],
                'distributions'   => Schema::hasTable('distributions') ? DB::table('distributions')->get() : [],
            ]
        ]);
    }

    public function exportSqliteFile(Request $request)
    {
        if (!$this->verifyKey($request)) {
            return response()->json(['error' => 'Unauthorized: Invalid sync key.'], 403);
        }

        $dbPath = config('database.connections.sqlite.database');
        if (!File::exists($dbPath)) {
            return response()->json(['error' => 'SQLite database file not found at: ' . $dbPath], 404);
        }

        return response()->download($dbPath, 'database.sqlite', [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    public function importSqliteFile(Request $request)
    {
        if (!$this->verifyKey($request)) {
            return response()->json(['error' => 'Unauthorized: Invalid sync key.'], 403);
        }

        if (!$request->hasFile('sqlite_file')) {
            return response()->json(['error' => 'No sqlite_file provided in request.'], 400);
        }

        $dbPath = config('database.connections.sqlite.database');
        
        // Create backup of current DB
        if (File::exists($dbPath)) {
            File::copy($dbPath, $dbPath . '.bak.' . time());
        }

        $uploaded = $request->file('sqlite_file');
        $uploaded->move(dirname($dbPath), basename($dbPath));

        Artisan::call('optimize:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'SQLite database replaced and cache cleared successfully.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function importJson(Request $request)
    {
        if (!$this->verifyKey($request)) {
            return response()->json(['error' => 'Unauthorized: Invalid sync key.'], 403);
        }

        $data = $request->input('data');
        if (!$data || !is_array($data)) {
            return response()->json(['error' => 'Invalid data payload.'], 400);
        }

        DB::beginTransaction();
        try {
            DB::statement('PRAGMA foreign_keys = OFF;');

            $tables = ['journal_entries', 'transactions', 'project_members', 'projects', 'accounts', 'distributions', 'funds', 'users'];
            foreach ($tables as $t) {
                if (Schema::hasTable($t)) {
                    DB::table($t)->truncate();
                }
            }

            if (!empty($data['users'])) DB::table('users')->insert(json_decode(json_encode($data['users']), true));
            if (!empty($data['funds'])) DB::table('funds')->insert(json_decode(json_encode($data['funds']), true));
            if (!empty($data['projects'])) DB::table('projects')->insert(json_decode(json_encode($data['projects']), true));
            if (!empty($data['project_members'])) DB::table('project_members')->insert(json_decode(json_encode($data['project_members']), true));
            if (!empty($data['accounts'])) DB::table('accounts')->insert(json_decode(json_encode($data['accounts']), true));
            if (!empty($data['transactions'])) DB::table('transactions')->insert(json_decode(json_encode($data['transactions']), true));
            if (!empty($data['journal_entries'])) DB::table('journal_entries')->insert(json_decode(json_encode($data['journal_entries']), true));
            if (!empty($data['distributions'])) DB::table('distributions')->insert(json_decode(json_encode($data['distributions']), true));

            DB::statement('PRAGMA foreign_keys = ON;');
            DB::commit();

            Artisan::call('optimize:clear');

            return response()->json([
                'status' => 'success',
                'message' => 'All database tables successfully restored from JSON payload.',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
