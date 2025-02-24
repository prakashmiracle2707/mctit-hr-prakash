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
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->string('task_name')->nullable()->after('milestone_id');
        });
    }

    public function down()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->dropColumn('task_name');
        });
    }
};
