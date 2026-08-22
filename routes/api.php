<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DatabaseSyncController;

Route::get('/db-dump', [DatabaseSyncController::class, 'export'])->name('api.dbDump');
Route::get('/db-sqlite', [DatabaseSyncController::class, 'exportSqliteFile'])->name('api.dbSqlite');
Route::post('/db-sqlite', [DatabaseSyncController::class, 'importSqliteFile'])->name('api.dbSqliteImport');
Route::post('/db-import', [DatabaseSyncController::class, 'importJson'])->name('api.dbImport');
