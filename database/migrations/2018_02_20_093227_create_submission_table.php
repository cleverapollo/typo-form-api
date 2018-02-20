<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organisation_id')->unsigned()->nullable();;
            $table->foreign('organisation_id')->references('id')
                ->on('organisations')->onDelete('cascade');
            $table->integer('user_id')->unsigned()->nullable();;
            $table->foreign('user_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->integer('form_id')->unsigned()->nullable();;
            $table->foreign('form_id')->references('id')
                ->on('forms')->onDelete('cascade');
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
        Schema::dropIfExists('submissions');
    }
}
