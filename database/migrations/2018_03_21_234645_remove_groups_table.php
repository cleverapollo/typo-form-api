<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('questions', function (Blueprint $table) {
		    $table->dropColumn('group_id');
	    });

	    Schema::dropIfExists('groups');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::create('groups', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('name');
		    $table->integer('section_id')->unsigned();
		    $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
		    $table->boolean('repeatable');
		    $table->timestamps();
	    });

	    Schema::table('questions', function (Blueprint $table) {
		    $table->integer('group_id')->nullable()->after('section_id');
	    });
    }
}
