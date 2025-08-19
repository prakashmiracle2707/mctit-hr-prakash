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
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->string('device_type_clockin')->nullable()->after('is_leave');
            $table->string('device_type_clockout')->nullable()->after('device_type_clockin');
        });
    }

    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['device_type_clockin', 'device_type_clockout']);
        });
    }
};
