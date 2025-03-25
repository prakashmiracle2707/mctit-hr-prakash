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
        Schema::create('complaints_office', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('issue_category_id')->nullable();
            $table->unsignedBigInteger('issue_title_id')->nullable();
            $table->string('subject')->nullable(); // Optional: custom subject
            $table->text('description');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])->default('Pending');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('issue_category_id')->references('id')->on('issue_categories')->onDelete('set null');
            $table->foreign('issue_title_id')->references('id')->on('issue_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
