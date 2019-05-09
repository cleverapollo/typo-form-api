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
		$this->call('ValidationTypeTableSeeder');
		$this->call('StatusesTableSeeder');
		$this->call('ComparatorsTableSeeder');
		$this->call('ActionTypesTableSeeder');
		$this->call('TriggerTypeTableSeeder');
        $this->call('AnswerSortsTableSeeder');
        $this->call('TypesTableSeeder');
        $this->call('CountriesTableSeeder');
        $this->call('AccessLevelTableSeeder');
	}
}
