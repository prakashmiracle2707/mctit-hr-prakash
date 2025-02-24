<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            if (!Schema::hasColumn('time_sheets', 'project_id')) {
                $table->bigInteger('project_id')->unsigned()->notNull()->after('id');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('time_sheets', function (Blueprint $table) {
            if (Schema::hasColumn('time_sheets', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
        });
    }
};
