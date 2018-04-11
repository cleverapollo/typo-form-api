<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validations', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('form_id');
	        $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
	        $table->unsignedInteger('question_id');
	        $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
	        $table->unsignedInteger('validation_type_id')->nullable();
	        $table->foreign('validation_type_id')->references('id')->on('validation_types')->onDelete('set null');
	        $table->string('validation_data')->nullable();
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
        Schema::dropIfExists('validations');
    }
}
