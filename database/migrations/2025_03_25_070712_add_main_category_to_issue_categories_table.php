<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMainCategoryToIssueCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('issue_categories', function (Blueprint $table) {
            $table->string('main_category')->default('IT-TICKET')->after('name');
        });
    }

    public function down()
    {
        Schema::table('issue_categories', function (Blueprint $table) {
            $table->dropColumn('main_category');
        });
    }
}

