<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateApplicationInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('application_invitations', 'invitations');
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign('application_invitations_application_id_foreign');
            $table->dropColumn(['token', 'application_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('invitations', 'application_invitations');
        Schema::table('application_invitations', function (Blueprint $table) {
            $table->unsignedInteger('application_id');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
            $table->string('token')->nullable()->unique();
        });
    }
}
