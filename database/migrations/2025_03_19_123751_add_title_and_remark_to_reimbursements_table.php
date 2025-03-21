<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->string('title')->after('employee_id'); // New Title field
            $table->text('remark')->nullable()->after('description'); // New Remark field
        });
    }

    public function down()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('remark');
        });
    }
};