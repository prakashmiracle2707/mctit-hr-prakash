<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('paid_by'); // Adding payment_type field
        });
    }

    public function down()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('payment_type'); // Rollback changes if needed
        });
    }
};
