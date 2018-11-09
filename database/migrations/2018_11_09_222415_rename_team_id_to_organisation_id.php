<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTeamIdToOrganisationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_users', function (Blueprint $table) {
            $table->renameColumn('team_id', 'organisation_id');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->renameColumn('team_id', 'organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_users', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'team_id');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->renameColumn('organisation_id', 'team_id');
        });
    }
}
