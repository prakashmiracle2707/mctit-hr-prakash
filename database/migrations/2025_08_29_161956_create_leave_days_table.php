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
        Schema::create('leave_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_id');
            $table->date('date');
            $table->decimal('leave_units', 4, 1); // 1.0 or 0.5
            $table->unsignedBigInteger('leave_type_id');
            $table->string('half_day_type')->nullable(); // morning|afternoon|full_day|null
            $table->timestamps();

            $table->foreign('leave_id')
                  ->references('id')->on('leaves')
                  ->onDelete('cascade');

            $table->index(['leave_id', 'date']);
            $table->unique(['leave_id', 'date']); // ensure one entry per leave/day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_days');
    }
};
