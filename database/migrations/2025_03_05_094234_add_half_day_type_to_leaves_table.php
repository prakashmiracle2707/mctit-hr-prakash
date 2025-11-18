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
        Schema::table('leaves', function (Blueprint $table) {
            $table->enum('half_day_type', ['full_day', 'morning', 'afternoon','leave_am_wfh_pm','wfh_am_leave_pm'])->default('full_day');
        });
    }

    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('half_day_type');
        });
    }
};
