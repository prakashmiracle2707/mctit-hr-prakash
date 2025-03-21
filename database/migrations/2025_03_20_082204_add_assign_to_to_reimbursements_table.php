<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->unsignedBigInteger('assign_to')->nullable()->after('status'); 
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('set null'); 
        });
    }

    public function down()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropForeign(['assign_to']);
            $table->dropColumn('assign_to');
        });
    }
};

