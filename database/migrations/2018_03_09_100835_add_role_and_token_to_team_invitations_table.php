<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleAndTokenToTeamInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_invitations', function (Blueprint $table) {
            $table->string('role')->default('User')->after('team_id');
            $table->string('token')->unique()->after('role');
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
            $table->dropColumn('token');
            $table->dropColumn('role');
        });
    }
}
