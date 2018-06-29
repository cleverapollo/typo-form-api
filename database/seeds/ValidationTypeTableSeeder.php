<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ValidationTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('validation_types')->delete();

	    DB::table('validation_types')->insert([
		    'type' => 'Number',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('validation_types')->insert([
		    'type' => 'Email',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('validation_types')->insert([
		    'type' => 'Words',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('validation_types')->insert([
		    'type' => 'Letters',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('validation_types')->insert([
		    'type' => 'Checkbox',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
