<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('roles')->delete();

	    DB::table('roles')->insert([
	    	'id' => 1,
		    'name' => 'Super Admin',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('roles')->insert([
		    'id' => 2,
		    'name' => 'Admin',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('roles')->insert([
		    'id' => 3,
		    'name' => 'User',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
