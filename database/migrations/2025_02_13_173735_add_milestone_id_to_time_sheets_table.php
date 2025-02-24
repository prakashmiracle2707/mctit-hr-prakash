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
            $table->unsignedBigInteger('milestone_id')->nullable()->after('project_id');
            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            $table->dropForeign(['milestone_id']);
            $table->dropColumn('milestone_id');
        });
    }

};
