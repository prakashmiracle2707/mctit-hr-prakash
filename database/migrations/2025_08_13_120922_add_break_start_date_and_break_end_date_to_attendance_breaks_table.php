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
        Schema::table('attendance_breaks', function (Blueprint $table) {
            $table->date('break_start_date')->nullable(); // Add the break_start_date column
            $table->date('break_end_date')->nullable();   // Add the break_end_date column
        });
    }

    public function down()
    {
        Schema::table('attendance_breaks', function (Blueprint $table) {
            $table->dropColumn(['break_start_date', 'break_end_date']);
        });
    }
};
