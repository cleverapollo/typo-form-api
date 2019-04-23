<?php

use App\Models\Comparator;
use Illuminate\Database\Seeder;

class ComparatorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'comparator' => 'equals'],
            ['id' => 2, 'comparator' => 'not equal to'],
            ['id' => 3, 'comparator' => 'less than'],
            ['id' => 4, 'comparator' => 'greater than'],
            ['id' => 5, 'comparator' => 'less than or equal to'],
            ['id' => 6, 'comparator' => 'greater than or equal to'],
            ['id' => 7, 'comparator' => 'contains'],
            ['id' => 8, 'comparator' => 'starts with'],
            ['id' => 9, 'comparator' => 'ends with'],
            ['id' => 10, 'comparator' => 'is null'],
            ['id' => 11, 'comparator' => 'is not null'],
            ['id' => 12, 'comparator' => 'in list'],
            ['id' => 13, 'comparator' => 'not in list'],
            ['id' => 14, 'comparator' => 'does not contain'],
            ['id' => 15, 'comparator' => 'is invalid'],
        ];

        foreach ($items as $item) {
            Comparator::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
