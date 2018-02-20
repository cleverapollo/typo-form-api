<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('login', 'UserController@login');
    $router->post('logout', 'UserController@logout');
    $router->post('register', 'UserController@register');
    $router->post('resetPassword', 'UserController@resetpassword');

    $router->post('organisations/','OrganisationController@store');
    $router->get('organisations/', 'OrganisationController@index');
    $router->get('organisations/{id}/', 'OrganisationController@show');
    $router->put('organisations/{id}/', 'OrganisationController@update');
    $router->delete('organisations/{id}/', 'OrganisationController@destroy');

    $router->post('forms/','FormController@store');
    $router->get('forms/', 'FormController@index');
    $router->get('forms/{id}/', 'FormController@show');
    $router->put('forms/{id}/', 'FormController@update');
    $router->delete('forms/{id}/', 'FormController@destroy');

    $router->group(['prefix' => 'forms/{form_id}'], function () use ($router) {
        $router->post('/sections/','SectionController@store');
        $router->get('/sections/', 'SectionController@index');
        $router->get('/sections/{id}/', 'SectionController@show');
        $router->put('/sections/{id}/', 'SectionController@update');
        $router->delete('/sections/{id}/', 'SectionController@destroy');
    });
});
