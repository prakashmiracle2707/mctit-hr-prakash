<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveManagersTable extends Migration
{
    public function up()
    {
        Schema::create('leave_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_id');
            $table->unsignedBigInteger('manager_id');
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->text('remark')->nullable();
            $table->timestamp('action_date')->nullable(); // when manager responded
            $table->timestamps();

            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_managers');
    }
}
