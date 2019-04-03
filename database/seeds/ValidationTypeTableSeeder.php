<?php

use App\Models\ValidationType;
use Illuminate\Database\Seeder;

class ValidationTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'type' => 'Number'],
            ['id' => 2, 'type' => 'Email'],
            ['id' => 3, 'type' => 'Words'],
            ['id' => 4, 'type' => 'Letters'],
            ['id' => 5, 'type' => 'Checkbox'],
            ['id' => 6, 'type' => 'Dropdown'],
            ['id' => 7, 'type' => 'Multiple choice'],
            ['id' => 8, 'type' => 'Decimal validation'],
            ['id' => 9, 'type' => 'Date after'],
            ['id' => 10, 'type' => 'Date before'],
            ['id' => 11, 'type' => 'Date between'],
            ['id' => 12, 'type' => 'Future date'],
            ['id' => 13, 'type' => 'Minimum Value'],
            ['id' => 14, 'type' => 'Maximum Value'],
            ['id' => 15, 'type' => 'Between']
        ];

        foreach ($items as $item) {
            ValidationType::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
