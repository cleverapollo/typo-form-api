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

    $router->group(['prefix' => 'sections/{section_id}'], function () use ($router) {
        $router->post('/groups/','GroupController@store');
        $router->get('/groups/', 'GroupController@index');
        $router->get('/groups/{id}/', 'GroupController@show');
        $router->put('/groups/{id}/', 'GroupController@update');
        $router->delete('/groups/{id}/', 'GroupController@destroy');
    });

    $router->post('questions/','QuestionController@store');
    $router->get('questions/', 'QuestionController@index');
    $router->get('questions/{id}/', 'QuestionController@show');
    $router->put('questions/{id}/', 'QuestionController@update');
    $router->delete('questions/{id}/', 'QuestionController@destroy');

    $router->group(['prefix' => 'questions/{question_id}'], function () use ($router) {
        $router->post('/answers/','AnswerController@store');
        $router->get('/answers/', 'AnswerController@index');
        $router->get('/answers/{id}/', 'AnswerController@show');
        $router->put('/answers/{id}/', 'AnswerController@update');
        $router->delete('/answers/{id}/', 'AnswerController@destroy');
    });

    $router->group(['prefix' => 'organisations/{organisation_id}'], function () use ($router) {
        $router->post('/submissions/','SubmissionController@store');
        $router->get('/submissions/', 'SubmissionController@index');
        $router->get('/submissions/{id}/', 'SubmissionController@show');
        $router->put('/submissions/{id}/', 'SubmissionController@update');
        $router->delete('/submissions/{id}/', 'SubmissionController@destroy');
    });

    $router->group(['prefix' => 'submissions/{submission_id}'], function () use ($router) {
        $router->post('/responses/','ResponseController@store');
        $router->get('/responses/', 'ResponseController@index');
        $router->get('/responses/{id}/', 'ResponseController@show');
        $router->put('/responses/{id}/', 'ResponseController@update');
        $router->delete('/responses/{id}/', 'ResponseController@destroy');
    });
});
