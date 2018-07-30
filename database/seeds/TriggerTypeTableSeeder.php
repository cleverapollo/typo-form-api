<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TriggerTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    // Delete all record
	    DB::table('trigger_types')->delete();

	    $question_type_id = DB::table('question_types')->where('type', 'Short answer')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'starts with')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'ends with')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Paragraph')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'starts with')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'ends with')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Multiple choice')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Checkboxes')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Dropdown')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not in list')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'File upload')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Linear scale')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than or equal to')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than or equal to')->first()->id,
		    'answer' => true,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Multiple choice grid')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => true,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => true,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Checkbox grid')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'contains')->first()->id,
		    'answer' => true,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'does not contain')->first()->id,
		    'answer' => true,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Date')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    $question_type_id = DB::table('question_types')->where('type', 'Time')->first()->id;

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'equals')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'not equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'less than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'greater than or equal to')->first()->id,
		    'answer' => false,
		    'value' => true,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);

	    DB::table('trigger_types')->insert([
		    'question_type_id' => $question_type_id,
		    'comparator_id' => DB::table('comparators')->where('comparator', 'is not null')->first()->id,
		    'answer' => false,
		    'value' => false,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }
}
