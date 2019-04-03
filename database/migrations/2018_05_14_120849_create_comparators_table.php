<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComparatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comparators', function (Blueprint $table) {
            $table->increments('id');
	        $table->string('comparator');
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => ComparatorsTableSeeder::class
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comparators');
    }
}
