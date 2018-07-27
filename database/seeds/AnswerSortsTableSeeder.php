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
	    	'sort' => 'Text ASC',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Text DESC',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Number ASC',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

        DB::table('answer_sorts')->insert([
	    	'sort' => 'Number DESC',
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
