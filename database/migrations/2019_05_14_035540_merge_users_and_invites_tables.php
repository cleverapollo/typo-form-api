<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class MergeUsersAndInvitesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => UserStatusesTableSeeder::class,
            '--force' => true,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->integer('status')->default(UserStatusRepository::idByLabel('Invited'))->after('remember_token');
            $table->bigInteger('workflow_delay')->unsigned()->default(0)->after('status');

            $table->foreign('status')->references('id')->on('user_statuses')->onDelete('restrict');
        });
        
        Schema::table('application_users', function (Blueprint $table) {
            $table->integer('status')->default(UserStatusRepository::idByLabel('Invited'))->after('role_id');
            $table->foreign('status')->references('id')->on('user_statuses')->onDelete('restrict');
        });
        
        Schema::table('organisation_users', function (Blueprint $table) {
            $table->integer('status')->default(UserStatusRepository::idByLabel('Invited'))->after('role_id');
            $table->foreign('status')->references('id')->on('user_statuses')->onDelete('restrict');
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
            $table->dropColumn('status');
            $table->dropColumn('workflow_delay');
        });

        Schema::table('application_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('organisation_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::dropIfExists('user_statuses');
    }
}
