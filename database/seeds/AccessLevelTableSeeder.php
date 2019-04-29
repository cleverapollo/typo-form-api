<?php

use App\Models\AccessLevel;
use Illuminate\Database\Seeder;

class AccessLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'label' => 'Private', 'value' => 'private'],
            ['id' => 2, 'label' => 'Application level', 'value' => 'internal'],
            ['id' => 3, 'label' => 'Public', 'value' => 'public'],
        ];

        foreach ($items as $item) {
            AccessLevel::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
