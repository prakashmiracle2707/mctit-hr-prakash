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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();                 // e.g., Bug, Story
            $table->string('color')->nullable();              // e.g., red, green
            $table->text('description')->nullable();          // e.g., "User-facing issue"
            $table->string('image')->nullable();              // e.g., uploaded file path
            $table->string('logo_name')->nullable();          // e.g., fas fa-bug, custom-icon

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
