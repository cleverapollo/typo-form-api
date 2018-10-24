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
		// Delete all record and reset auto_increments
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('question_types')->truncate();

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
			'type' => 'Multiple choice',
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

		DB::table('question_types')->insert([
			'type' => 'Content Block',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

		DB::table('question_types')->insert([
			'type' => 'ABN Lookup',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		]);

        DB::table('question_types')->insert([
            'type' => 'Number',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('question_types')->insert([
            'type' => 'Decimal',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('question_types')->insert([
            'type' => 'Email',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('question_types')->insert([
            'type' => 'Percent',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
		]);
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
	}
}
