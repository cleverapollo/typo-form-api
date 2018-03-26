<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoleAtUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('users', function (Blueprint $table) {
		    $table->integer('role_id')->unsigned()->default(3)->after('password');
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
	    Schema::table('users', function (Blueprint $table) {
		    $table->string('role')->default('Normal')->after('password');
		    $table->dropForeign(['role_id']);
		    $table->dropColumn('role_id');
	    });
    }
}
