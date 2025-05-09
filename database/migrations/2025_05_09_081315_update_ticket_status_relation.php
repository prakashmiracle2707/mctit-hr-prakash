<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status'); // Remove old status string column
            $table->unsignedBigInteger('status')->nullable()->after('ticket_created');

            $table->foreign('status')
                  ->references('id')
                  ->on('ticket_statuses')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->dropForeign(['status']);
            $table->dropColumn('status');
        });
    }
};
