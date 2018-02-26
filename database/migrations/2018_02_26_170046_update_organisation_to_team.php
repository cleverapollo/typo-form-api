<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrganisationToTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('organisations', 'teams');
        Schema::rename('user_organisation', 'user_team');

        Schema::table('submissions', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'team_id');
        });

        Schema::table('user_team', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('teams', 'organisations');
        Schema::rename('user_team', 'user_organisation');

        Schema::table('submissions', function (Blueprint $table) {
            $table->renameColumn('team_id', 'organisation_id');
        });

        Schema::table('user_team', function (Blueprint $table) {
            $table->renameColumn('team_id', 'organisation_id');
        });
    }
}
