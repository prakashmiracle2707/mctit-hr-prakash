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
        Schema::create('issue_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_category_id')
                  ->constrained('issue_categories')
                  ->onDelete('cascade');
            $table->string('name'); // Example: Mouse, VPN not working
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_titles');
    }
};
