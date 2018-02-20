<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('answer_id')->unsigned()->nullable();;
            $table->foreign('answer_id')->references('id')
                ->on('answers')->onDelete('cascade');
            $table->integer('submission_id')->unsigned()->nullable();;
            $table->foreign('submission_id')->references('id')
                ->on('submissions')->onDelete('cascade');
            $table->string('response');
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
        Schema::dropIfExists('responses');
    }
}
