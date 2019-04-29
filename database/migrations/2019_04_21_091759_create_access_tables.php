<?php

use App\Models\AccessLevel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Silber\Bouncer\Database\Models;

class CreateAccessTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->string('value');
            $table->timestamps();

            $table->index('value', 'access_levels_value_index');
        });

        Artisan::call('db:seed', [
            '--class' => AccessLevelTableSeeder::class,
            '--force' => true,
        ]);

        Schema::create('access_settings', function (Blueprint $table) {
            $accessLevelId = AccessLevel::where('value', '=', 'private')->first()->id;

            $table->increments('id');
            $table->unsignedInteger('access_level_id')->default($accessLevelId);
            $table->integer('resource_id')->unsigned()->nullable();
            $table->string('resource_type')->nullable();
            $table->timestamps();

            $table->foreign('access_level_id')
                ->references('id')
                ->on('access_levels')
                ->onDelete('cascade');

            $table->index(['resource_id', 'resource_type'], 'access_settings_resource_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('access_settings');
        Schema::drop('access_levels');
    }
}
