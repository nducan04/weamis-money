<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
                $table->rememberToken()->after('password');
            });
        }

        if (!Schema::hasColumn('projects', 'created_by_user_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('lead_user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['password', 'remember_token']);
            });
        }

        if (Schema::hasColumn('projects', 'created_by_user_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            });
        }
    }
};
