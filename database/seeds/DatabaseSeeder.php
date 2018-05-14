<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        $this->call('RolesTableSeeder');
		$this->call('QuestionTypeTableSeeder');
		$this->call('PeriodsTableSeeder');
		$this->call('ValidationTypeTableSeeder');
		$this->call('StatusesTableSeeder');
		$this->call('ComparatorsTableSeeder');
	}
}
