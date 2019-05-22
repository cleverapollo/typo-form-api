<?php

use App\Models\Application;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;

class UrlServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.app.frontend_url', 'example.org:7777');
        Config::set('services.app.frontend_scheme', 'http');
    }

    public function test_url_service_get_application_url()
    {
        $application = new Application;
        $application->slug = 'foofoo';

        $this->assertEquals('http://foofoo.example.org:7777', UrlService::getApplication($application));
    }

    public function test_url_service_get_login_url()
    {
        $application = new Application;
        $application->slug = 'foofoo';

        $data = urlencode(base64_encode(json_encode(['email' => 'a@a.com'])));

        $this->assertEquals(
            "http://foofoo.example.org:7777/login?invite=$data",
            UrlService::getApplicationLogin($application, ['email' => 'a@a.com'])
        );

        $this->assertEquals(
            'http://foofoo.example.org:7777/login', 
            UrlService::getApplicationLogin($application, null)
        );
    }


    public function test_url_service_get_register_url()
    {
        $application = new Application;
        $application->slug = 'foofoo';

        $parameters = ['first_name' => 'Chris', 'last_name' => 'Kelly', 'email' => 'a@a.com'];
        $data = urlencode(base64_encode(json_encode($parameters)));
        $this->assertEquals(
            "http://foofoo.example.org:7777/register?invite=$data",
            UrlService::getApplicationRegister($application, $parameters)
        );

        $this->assertEquals(
            'http://foofoo.example.org:7777/register', 
            UrlService::getApplicationRegister($application, null)
        );
    }
}