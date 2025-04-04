<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('it_tickets', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('it_tickets', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
