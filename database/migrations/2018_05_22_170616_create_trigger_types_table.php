<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_types', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('question_type_id')->nullable();
	        $table->foreign('question_type_id')->references('id')->on('question_types')->onDelete('set null');
	        $table->unsignedInteger('comparator_id')->nullable();
	        $table->foreign('comparator_id')->references('id')->on('comparators')->onDelete('set null');
	        $table->boolean('answer');
	        $table->boolean('value');
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
        Schema::dropIfExists('trigger_types');
    }
}
