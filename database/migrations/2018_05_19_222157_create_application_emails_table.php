<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_emails', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('application_id');
	        $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
	        $table->string('recipients');
	        $table->string('subject');
	        $table->text('body');
	        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_emails');
    }
}
