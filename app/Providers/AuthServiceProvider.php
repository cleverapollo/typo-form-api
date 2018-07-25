<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('API-Token')) {
                $user = User::where([
                    ['expire_date', '>', Carbon::now()->subMinutes(15)],
                    ['api_token', '=', $request->header('API-Token')]
                ])->first();

                if (!is_null($user)) {
                    $expire_date = Carbon::now();
                    $user->update(['expire_date' => $expire_date]);
                }
                return $user;
            }
        });
    }
}
