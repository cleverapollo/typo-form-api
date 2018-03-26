<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoleAtTeamInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_invitations', function (Blueprint $table) {
	        $table->integer('role_id')->unsigned()->default(3)->after('team_id');
	        $table->foreign('role_id')->references('id')->on('roles');
	        $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_invitations', function (Blueprint $table) {
	        $table->string('role')->default('User')->after('team_id');
	        $table->dropForeign(['role_id']);
	        $table->dropColumn('role_id');
        });
    }
}
