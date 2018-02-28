<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNameAsText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('question')->change();
            $table->text('description')->change();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->text('answer')->change();
        });

        Schema::table('responses', function (Blueprint $table) {
            $table->text('response')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question')->change();
            $table->string('description')->change();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->string('answer')->change();
        });

        Schema::table('responses', function (Blueprint $table) {
            $table->string('response')->change();
        });
    }
}
