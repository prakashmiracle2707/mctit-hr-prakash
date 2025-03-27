<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('secondary_role_assign', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('secondary_role_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('secondary_role_id')->references('id')->on('secondary_roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secondary_role_assign');
    }
};