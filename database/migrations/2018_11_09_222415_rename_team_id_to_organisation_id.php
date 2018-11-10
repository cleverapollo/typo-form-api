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
            $table->dropForeign('team_users_team_id_foreign');
            $table->renameColumn('team_id', 'organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_team_id_foreign');
            $table->renameColumn('team_id', 'organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
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
            $table->dropForeign('team_users_organisation_id_foreign');
            $table->renameColumn('organisation_id', 'team_id');
            $table->foreign('team_id')->references('id')->on('organisations')->onDelete('cascade');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_organisation_id_foreign');
            $table->renameColumn('organisation_id', 'team_id');
            $table->foreign('team_id')->references('id')->on('organisations')->onDelete('cascade');
        });
    }
}
