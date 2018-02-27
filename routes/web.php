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
    $router->get('user-info', 'UserController@userInfo');
    $router->post('logout', 'UserController@logout');
    $router->post('register', 'UserController@register');
    $router->post('reset-password', 'UserController@resetPassword');

    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->post('/', 'UserController@register');
        $router->get('/', 'UserController@index');
        $router->get('{id}', 'UserController@show');
        $router->put('{id}', 'UserController@update');
        $router->delete('{id}', 'UserController@destroy');
    });

    $router->group(['prefix' => 'teams'], function () use ($router) {
        $router->post('/', 'TeamController@store');
        $router->get('/', 'TeamController@index');
        $router->get('{id}', 'TeamController@show');
        $router->put('{id}', 'TeamController@update');
        $router->delete('{id}', 'TeamController@destroy');

        $router->group(['prefix' => '{team_id}/submissions'], function () use ($router) {
            $router->post('/', 'SubmissionController@store');
            $router->get('/', 'SubmissionController@index');
            $router->get('{id}', 'SubmissionController@show');
            $router->put('{id}', 'SubmissionController@update');
            $router->delete('{id}', 'SubmissionController@destroy');
        });
    });

    $router->group(['prefix' => 'forms'], function () use ($router) {
        $router->post('/', 'FormController@store');
        $router->get('/', 'FormController@index');
        $router->get('{id}', 'FormController@show');
        $router->put('{id}', 'FormController@update');
        $router->delete('{id}', 'FormController@destroy');

        $router->group(['prefix' => '{form_id}/sections'], function () use ($router) {
            $router->post('/', 'SectionController@store');
            $router->get('/', 'SectionController@index');
            $router->get('{id}', 'SectionController@show');
            $router->put('{id}', 'SectionController@update');
            $router->delete('{id}', 'SectionController@destroy');
        });
    });

    $router->group(['prefix' => 'sections/{section_id}/groups'], function () use ($router) {
        $router->post('/', 'GroupController@store');
        $router->get('/', 'GroupController@index');
        $router->get('{id}', 'GroupController@show');
        $router->put('{id}', 'GroupController@update');
        $router->delete('{id}', 'GroupController@destroy');
    });


    $router->group(['prefix' => 'questions'], function () use ($router) {
        $router->post('/', 'QuestionController@store');
        $router->get('/', 'QuestionController@index');
        $router->get('{id}', 'QuestionController@show');
        $router->put('{id}', 'QuestionController@update');
        $router->delete('{id}', 'QuestionController@destroy');

        $router->group(['prefix' => '{question_id}/answers'], function () use ($router) {
            $router->post('/', 'AnswerController@store');
            $router->get('/', 'AnswerController@index');
            $router->get('{id}', 'AnswerController@show');
            $router->put('{id}', 'AnswerController@update');
            $router->delete('{id}', 'AnswerController@destroy');
        });
    });

    $router->group(['prefix' => 'submissions/{submission_id}/responses'], function () use ($router) {
        $router->post('/', 'ResponseController@store');
        $router->get('/', 'ResponseController@index');
        $router->get('{id}', 'ResponseController@show');
        $router->put('{id}', 'ResponseController@update');
        $router->delete('{id}', 'ResponseController@destroy');
    });
});
