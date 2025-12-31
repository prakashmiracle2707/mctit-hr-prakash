<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeverityToTicketsTable extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // small string is enough; you can also use enum if you prefer
            $table->string('severity')->nullable()->after('priority')->default('Low');
        });

        // Optional: backfill existing rows with default 'Low' (if you didn't set default above)
        // DB::table('tickets')->update(['severity' => 'Low']);
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }
}
