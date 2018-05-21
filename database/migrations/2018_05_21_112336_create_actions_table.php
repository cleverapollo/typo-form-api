<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('user_id');
	        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
	        $table->unsignedInteger('action_id');
	        $table->unsignedInteger('action_type_id')->nullable();
	        $table->foreign('action_type_id')->references('id')->on('action_types')->onDelete('set null');
	        $table->timestamp('trigger_at');
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
        Schema::dropIfExists('actions');
    }
}
