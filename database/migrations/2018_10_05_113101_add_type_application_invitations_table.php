<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeApplicationInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->unsignedInteger('type_id')->default(1);
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
            $table->unsignedInteger('reference_id')->default(0);
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
            $table->dropForeign('invitations_type_id_foreign');
            $table->dropColumn(['type_id', 'reference_id']);
        });
    }
}
