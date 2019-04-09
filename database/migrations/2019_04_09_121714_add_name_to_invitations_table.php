<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('inviter_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->renameColumn('invitee', 'email');
            $table->text('properties')->nullable()->after('invitee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('first_name', 'last_name', 'properties');
            $table->renameColumn('email', 'invitee');
        });
    }
}
