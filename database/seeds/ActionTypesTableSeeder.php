<?php

use App\Models\ActionType;
use Illuminate\Database\Seeder;

class ActionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'type' => 'send email'],
            ['id' => 2, 'type' => 'create form template'],
            ['id' => 3, 'type' => 'create form'],
            ['id' => 4, 'type' => 'send form']
        ];

        foreach ($items as $item) {
            ActionType::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
