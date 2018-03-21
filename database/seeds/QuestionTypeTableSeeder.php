<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionTypeTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('question_types')->insert([
			'type' => 'Short answer',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Paragraph',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Multiple Choice',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Checkboxes',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Dropdown',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'File upload',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Linear scale',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Multiple choice grid',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Checkbox grid',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Date',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'Time',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);
	}
}
