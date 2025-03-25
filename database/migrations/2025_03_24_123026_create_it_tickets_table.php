<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('it_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('issue_category_id')->constrained('issue_categories')->onDelete('cascade');
            $table->foreignId('issue_title_id')->constrained('issue_titles')->onDelete('cascade');
            $table->text('description');
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('it_tickets');
    }
};
