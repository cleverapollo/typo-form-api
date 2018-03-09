<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleAndTokenToApplicationInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_invitations', function (Blueprint $table) {
            $table->string('role')->default('User')->after('application_id');
            $table->string('token')->after('role');
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
            $table->dropColumn('token');
            $table->dropColumn('role');
        });
    }
}
