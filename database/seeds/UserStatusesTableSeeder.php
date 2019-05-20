<?php

use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UserStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => 1, 'label' => 'Invited'],
            ['id' => 2, 'label' => 'Active'],
            ['id' => 3, 'label' => 'Suspended'],
            ['id' => 4, 'label' => 'Unregistered'],
            ['id' => 5, 'label' => 'Registered'],
        ];

        foreach ($items as $item) {
            UserStatus::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
