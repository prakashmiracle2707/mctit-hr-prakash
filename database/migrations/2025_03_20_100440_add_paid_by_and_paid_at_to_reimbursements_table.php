<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_by')->nullable()->after('approved_at'); // User who marked as paid
            $table->timestamp('paid_at')->nullable()->after('paid_by'); // Date and time of payment
            
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null'); // Foreign key
        });
    }

    public function down()
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['paid_by', 'paid_at']);
        });
    }
};

