<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class StatusRepositoryTest extends TestCase
{
    // use DatabaseMigrations;

    public function test_type_repository_fetch_by_id_methods()
    {
        $this->assertEquals(StatusRepository::nameById(1), 'Open');
        $this->assertEquals(StatusRepository::nameById(2), 'Closed');
        $this->assertEquals(StatusRepository::nameById(3), 'Archived');
        $this->assertEquals(StatusRepository::nameById(4), 'Locked');
    }

    public function test_type_repository_fetch_by_name_methods()
    {
        $this->assertEquals(StatusRepository::idByName('Open'), 1);
        $this->assertEquals(StatusRepository::idByName('Closed'), 2);
        $this->assertEquals(StatusRepository::idByName('Archived'), 3);
        $this->assertEquals(StatusRepository::idByName('Locked'), 4);
    }

    public function test_type_repository_dictionary()
    {
        $this->assertEquals(StatusRepository::dictionary(1), [
            'value' => 1,
            'label' => 'Open',
        ]);
        $this->assertEquals(StatusRepository::dictionary(2), [
            'value' => 2,
            'label' => 'Closed',
        ]);
        $this->assertEquals(StatusRepository::dictionary(3), [
            'value' => 3,
            'label' => 'Archived',
        ]);
        $this->assertEquals(StatusRepository::dictionary(4), [
            'value' => 4,
            'label' => 'Locked',
        ]);
    }
}