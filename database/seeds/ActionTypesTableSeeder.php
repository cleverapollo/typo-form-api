<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('action_types')->delete();

	    DB::table('action_types')->insert([
		    'type' => 'send email',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('action_types')->insert([
		    'type' => 'create form',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('action_types')->insert([
		    'type' => 'create submit',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('action_types')->insert([
		    'type' => 'send submit',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
