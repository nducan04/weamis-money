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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('user, project, fund, external'); // Phân loại tài khoản
            $table->nullableMorphs('owner'); // owner_type, owner_id (trỏ đến User, Project, Fund)
            $table->string('name')->comment('Tên Ví');
            $table->decimal('balance', 15, 2)->default(0)->comment('Số dư cache');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
