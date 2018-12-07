<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameFormsToFormTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('forms', 'form_templates');

        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign('sections_form_id_foreign');
            $table->renameColumn('form_id', 'form_template_id');
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('validations', function (Blueprint $table) {
            $table->dropForeign('validations_form_id_foreign');
            $table->renameColumn('form_id', 'form_template_id');
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('question_triggers', function (Blueprint $table) {
            $table->dropForeign('question_triggers_form_id_foreign');
            $table->renameColumn('form_id', 'form_template_id');
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_form_id_foreign');
            $table->renameColumn('form_id', 'form_template_id');
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign('sections_form_template_id_foreign');
            $table->renameColumn('form_template_id', 'form_id');
            $table->foreign('form_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('validations', function (Blueprint $table) {
            $table->dropForeign('validations_form_template_id_foreign');
            $table->renameColumn('form_template_id', 'form_id');
            $table->foreign('form_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('question_triggers', function (Blueprint $table) {
            $table->dropForeign('question_triggers_form_template_id_foreign');
            $table->renameColumn('form_template_id', 'form_id');
            $table->foreign('form_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_form_template_id_foreign');
            $table->renameColumn('form_template_id', 'form_id');
            $table->foreign('form_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        Schema::rename('form_templates', 'forms');
    }
}
