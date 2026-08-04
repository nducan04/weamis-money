<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        // Execute DatabaseSeeder to force re-sync database state to exact 41 Google Sheet transactions
        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\DatabaseSeeder',
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // No-op
    }
};
