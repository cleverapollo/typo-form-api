<?php

use App\Models\QuestionType;
use Illuminate\Database\Seeder;

class QuestionTypeTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        $items = [
            ['id' => 1, 'type' => 'Short answer'],
            ['id' => 2, 'type' => 'Paragraph'],
            ['id' => 3, 'type' => 'Multiple choice'],
            ['id' => 4, 'type' => 'Checkboxes'],
            ['id' => 5, 'type' => 'Dropdown'],
            ['id' => 6, 'type' => 'File upload'],
            ['id' => 7, 'type' => 'Linear scale'],
            ['id' => 8, 'type' => 'Multiple choice grid'],
            ['id' => 9, 'type' => 'Checkbox grid'],
            ['id' => 10, 'type' => 'Date'],
            ['id' => 11, 'type' => 'Time'],
            ['id' => 12, 'type' => 'Content Block'],
            ['id' => 13, 'type' => 'ABN Lookup'],
            ['id' => 14, 'type' => 'Number'],
            ['id' => 15, 'type' => 'Decimal'],
            ['id' => 16, 'type' => 'Email'],
            ['id' => 17, 'type' => 'Percent'],
            ['id' => 18, 'type' => 'Phone number'],
            ['id' => 19, 'type' => 'Address'],
            ['id' => 20, 'type' => 'URL'],
            ['id' => 21, 'type' => 'Country'],
            ['id' => 22, 'type' => 'Lookup']
        ];

        foreach ($items as $item) {
            QuestionType::updateOrCreate(['id' => $item['id']], $item);
        }
	}
}
