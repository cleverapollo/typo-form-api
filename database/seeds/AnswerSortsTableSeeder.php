<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnswerSortsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// Delete all record
	    DB::table('answer_sorts')->delete();

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Default',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Alphanumeric Ascending (A-Z)',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Alphanumeric Descending (Z-A)',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Number Ascending (1-9)',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Number Descending (9-1)',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
