<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToQuestionTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_triggers', function (Blueprint $table) {
            $table->text('type')->nullable()->after('id');
            $table->dropForeign('question_triggers_question_id_foreign');
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
            $table->dropColumn('type');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }
}
