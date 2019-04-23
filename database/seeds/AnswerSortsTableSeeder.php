<?php

use App\Models\AnswerSort;
use Illuminate\Database\Seeder;

class AnswerSortsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'sort' => 'Default'],
            ['id' => 2, 'sort' => 'Alphanumeric Ascending (A-Z)'],
            ['id' => 3, 'sort' => 'Alphanumeric Descending (Z-A)'],
            ['id' => 4, 'sort' => 'Number Ascending (1-9)'],
            ['id' => 5, 'sort' => 'Number Descending (9-1)']
        ];

        foreach ($items as $item) {
            AnswerSort::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
