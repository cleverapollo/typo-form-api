<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTeamInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('team_invitations');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inviter_id');
            $table->foreign('inviter_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('invitee');
            $table->unsignedInteger('team_id');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->unsignedInteger('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            $table->string('token')->nullable()->unique();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }
}
