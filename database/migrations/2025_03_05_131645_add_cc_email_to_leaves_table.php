<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCcEmailToLeavesTable extends Migration
{
    public function up()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->json('cc_email')->nullable(); // Store CC emails as JSON
        });
    }

    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('cc_email');
        });
    }
}
