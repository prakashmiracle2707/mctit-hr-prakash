<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->integer('workhours')->nullable()->after('hours');
            $table->integer('workminutes')->nullable()->after('workhours');
        });
    }

    public function down()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->dropColumn(['workhours', 'workminutes']);
        });
    }
};
