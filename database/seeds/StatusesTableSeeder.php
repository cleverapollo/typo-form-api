<?php

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'status' => 'Open'],
            ['id' => 2, 'status' => 'Closed'],
            ['id' => 3, 'status' => 'Archived'],
            ['id' => 4, 'status' => 'Locked']
        ];

        foreach ($items as $item) {
            Status::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
