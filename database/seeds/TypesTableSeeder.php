<?php

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'name' => 'application'],
            ['id' => 2, 'name' => 'organisation']
        ];

        foreach ($items as $item) {
            Type::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
