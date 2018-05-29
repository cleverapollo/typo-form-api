<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePeriodsFieldsFromFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
	        $table->dropForeign(['period_id']);
            $table->dropColumn(['period_start', 'period_end', 'period_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
	        $table->timestamp('period_start')->nullable()->after('application_id');
	        $table->timestamp('period_end')->nullable()->after('period_start');
	        $table->unsignedInteger('period_id')->nullable()->after('period_end');
	        $table->foreign('period_id')->references('id')->on('periods')->onDelete('set null');
        });
    }
}
