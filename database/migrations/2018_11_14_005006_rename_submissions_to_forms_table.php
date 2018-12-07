<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSubmissionsToFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('submissions', 'forms');

        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign('responses_submission_id_foreign');
            $table->renameColumn('submission_id', 'form_id');
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
        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign('responses_form_id_foreign');
            $table->renameColumn('form_id', 'submission_id');
            $table->foreign('submission_id')->references('id')->on('forms')->onDelete('cascade');
        });

        Schema::rename('forms', 'submissions');
    }
}
