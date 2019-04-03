<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_triggers', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('question_id');
	        $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
	        $table->unsignedInteger('parent_question_id');
	        $table->foreign('parent_question_id')->references('id')->on('questions')->onDelete('cascade');
	        $table->unsignedInteger('parent_answer_id')->nullable();
	        $table->foreign('parent_answer_id')->references('id')->on('answers')->onDelete('set null');
	        $table->string('value')->nullable();
	        $table->unsignedInteger('comparator_id')->nullable();
	        $table->foreign('comparator_id')->references('id')->on('comparators')->onDelete('set null');
	        $table->unsignedInteger('order');
	        $table->boolean('operator');
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
        Schema::dropIfExists('question_triggers');
    }
}
