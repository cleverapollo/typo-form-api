<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class TypeRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_type_repository_fetch_by_id_methods()
    {
        $this->assertEquals(TypeRepository::nameById(1), 'application');
        $this->assertEquals(TypeRepository::nameById(2), 'organisation');
    }

    public function test_type_repository_fetch_by_name_methods()
    {
        $this->assertEquals(TypeRepository::idByName('application'), 1);
        $this->assertEquals(TypeRepository::idByName('organisation'), 2);
    }

    public function test_type_repository_dictionary()
    {
        $this->assertEquals(TypeRepository::dictionary(1), [
            'value' => 1,
            'label' => 'application',
        ]);
        $this->assertEquals(TypeRepository::dictionary(2), [
            'value' => 2,
            'label' => 'organisation',
        ]);
    }
}