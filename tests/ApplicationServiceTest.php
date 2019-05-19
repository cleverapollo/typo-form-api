<?php

use App\User;
use App\Models\Application;
use App\Services\ApplicationService;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ApplicationServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_invite_user_creates_user_when_does_not_exist()
    {
        $applicationService = new ApplicationService;

        Application::create([
            'id' => 1,
            'name' => 'test-app',
            'share_token' => 'share-token-123',
            'slug' => 'test-app',
        ]);
        $this->assertEquals(User::whereEmail('test@test.com')->count(), 0);

        $applicationService->inviteUser([
            'firstname' => 'bob',
            'lastname' => 'jones',
            'role_id' => 3,
            'application_id' => 1,
            'user_id' => 1,
            'meta' => [],
            'invitation' => [
                'email' => 'test@test.com',
            ],
        ]);

        $this->assertEquals(User::whereEmail('test@test.com')->count(), 1);
    }
}