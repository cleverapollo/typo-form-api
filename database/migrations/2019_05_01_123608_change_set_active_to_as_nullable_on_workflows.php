<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSetActiveToAsNullableOnWorkflows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->datetime('active_to')->nullable()->change();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // TODO Temp "unnull" values - all this will be flattened before merged into master
        App\Models\Workflow::whereNull('active_to')->update(['active_to' => '20990101']);
        Schema::table('workflows', function (Blueprint $table) {
            $table->datetime('active_to')->nullable(false)->change();
        });
    }
}
