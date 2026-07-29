<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('member'); // 'admin' or 'member'
            $table->string('avatar')->nullable();
            $table->decimal('share_percentage', 5, 2)->default(0.00); // Tỷ lệ % cổ phần/đóng góp
            $table->decimal('current_debt', 15, 2)->default(0.00); // Dư nợ vay cá nhân từ quỹ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
