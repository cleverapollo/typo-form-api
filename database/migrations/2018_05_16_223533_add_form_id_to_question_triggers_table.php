<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormIdToQuestionTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_triggers', function (Blueprint $table) {
	        $table->unsignedInteger('form_id')->after('id');
	        $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_triggers', function (Blueprint $table) {
	        $table->dropForeign(['form_id']);
	        $table->dropColumn('form_id');
        });
    }
}
