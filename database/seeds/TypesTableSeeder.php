<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('types')->delete();

	    DB::table('types')->insert([
		    'name' => 'application',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('types')->insert([
		    'name' => 'organisation',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
