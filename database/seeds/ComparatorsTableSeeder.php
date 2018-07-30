<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComparatorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('comparators')->delete();

	    DB::table('comparators')->insert([
	    	'comparator' => 'equals',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

		DB::table('comparators')->insert([
			'comparator' => 'not equal to',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

	    DB::table('comparators')->insert([
		    'comparator' => 'less than',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'greater than',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'less than or equal to',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'greater than or equal to',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'contains',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'starts with',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'ends with',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'is null',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'is not null',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'in list',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'not in list',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('comparators')->insert([
		    'comparator' => 'does not contain',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
