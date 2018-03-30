<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeAnswerIdAsNullableAtResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('responses', function (Blueprint $table) {
        	$table->dropForeign(['answer_id']);
	        $table->integer('answer_id')->unsigned()->nullable()->default(null)->change();
	        $table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('responses', function (Blueprint $table) {
	        $table->dropForeign(['answer_id']);
	        $table->integer('answer_id')->unsigned()->change();
	        $table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade');
        });
    }
}
