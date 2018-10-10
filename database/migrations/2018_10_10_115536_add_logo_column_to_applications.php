<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogoColumnToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('logo')->nullable()->after('icon');
            $table->text('primary_color')->nullable()->after('icon');
            $table->text('secondary_color')->nullable()->after('icon');
            $table->text('background_image')->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropColumn('primary_color');
            $table->dropColumn('secondary_color');
            $table->dropColumn('background_image');
        });
    }
}
