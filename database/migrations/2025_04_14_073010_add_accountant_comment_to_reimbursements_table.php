<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->text('accountant_comment')->nullable()->after('remark');
        });
    }

    public function down()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('accountant_comment');
        });
    }
};
