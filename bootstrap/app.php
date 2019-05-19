<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

$app->withEloquent();

$app->configure('filesystems');

$app->configure('database');

$app->configure('services');

$app->configure('mail');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
   App\Http\Middleware\CorsMiddleware::class,
]);
   
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'resolve-application-slug' => App\Http\Middleware\ResolveApplicationSlugMiddleware::class,
    'application-admin' => App\Http\Middleware\ApplicationAdminMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(\Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(DynEd\Lumen\MaintenanceMode\MaintenanceModeServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Silber\Bouncer\BouncerServiceProvider::class);


// Clockwork Debugging Tool
if (env('APP_DEBUG')) {
    $app->withEloquent();
    $app->register(Clockwork\Support\Lumen\ClockworkServiceProvider::class);
}

$app->alias('mailer', \Illuminate\Contracts\Mail\Mailer::class);
$app->alias('Response', Illuminate\Support\Facades\Response::class);

if(!class_exists('Bouncer')) {
    class_alias(Silber\Bouncer\BouncerFacade::class, 'Bouncer');
}
if(!class_exists('Acl')) {
    class_alias(App\Services\AclFacade::class, 'Acl');
}
if(!class_exists('MailService')) {
    class_alias(App\Services\MailFacade::class, 'MailService');
}
if(!class_exists('ApplicationRepository')) {
    class_alias(App\Repositories\ApplicationRepositoryFacade::class, 'ApplicationRepository');
}
if(!class_exists('ApplicationUserRepository')) {
    class_alias(App\Repositories\ApplicationUserRepositoryFacade::class, 'ApplicationUserRepository');
}
if(!class_exists('OrganisationRepository')) {
    class_alias(App\Repositories\OrganisationRepositoryFacade::class, 'OrganisationRepository');
}
if(!class_exists('OrganisationUserRepository')) {
    class_alias(App\Repositories\OrganisationUserRepositoryFacade::class, 'OrganisationUserRepository');
}
if(!class_exists('RoleRepository')) {
    class_alias(App\Repositories\RoleRepositoryFacade::class, 'RoleRepository');
}
if(!class_exists('UserRepository')) {
    class_alias(App\Repositories\UserRepositoryFacade::class, 'UserRepository');
}
if(!class_exists('UserStatusRepository')) {
    class_alias(App\Repositories\UserStatusRepositoryFacade::class, 'UserStatusRepository');
}
if(!class_exists('WorkflowRepository')) {
    class_alias(App\Repositories\WorkflowRepositoryFacade::class, 'WorkflowRepository');
}
if(!class_exists('Illuminate\Support\Facades\Response')) {
    class_alias('Illuminate\Support\Facades\Response','Response');
}
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
