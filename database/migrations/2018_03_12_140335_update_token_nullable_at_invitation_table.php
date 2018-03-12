<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTokenNullableAtInvitationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_invitations', function (Blueprint $table) {
            $table->string('token')->nullable()->change();
        });

        Schema::table('team_invitations', function (Blueprint $table) {
            $table->string('token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_invitations', function (Blueprint $table) {
            $table->string('token')->change();
        });

        Schema::table('team_invitations', function (Blueprint $table) {
            $table->string('token')->change();
        });
    }
}
