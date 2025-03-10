<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->boolean('work_from_home')->default(false)->after('clock_out'); // Add after clock_out field
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn('work_from_home');
        });
    }
};

