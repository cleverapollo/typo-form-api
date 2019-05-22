<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class RoleRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_role_repository_fetch_by_id_methods()
    {
        $this->assertEquals(RoleRepository::nameById(1), 'Super Admin');
        $this->assertEquals(RoleRepository::nameById(2), 'Admin');
        $this->assertEquals(RoleRepository::nameById(3), 'User');
    }

    public function test_role_repository_fetch_by_name_methods()
    {
        $this->assertEquals(RoleRepository::idByName('Super Admin'), 1);
        $this->assertEquals(RoleRepository::idByName('Admin'), 2);
        $this->assertEquals(RoleRepository::idByName('User'), 3);
    }

    public function test_role_repository_dictionary()
    {
        $this->assertEquals(RoleRepository::dictionary(1), [
            'value' => 1,
            'label' => 'Super Admin',
        ]);
        $this->assertEquals(RoleRepository::dictionary(2), [
            'value' => 2,
            'label' => 'Admin',
        ]);
        $this->assertEquals(RoleRepository::dictionary(3), [
            'value' => 3,
            'label' => 'User',
        ]);
    }
}