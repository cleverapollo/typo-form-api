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
	$router->post('login', 'Auth\LoginController@login');
	$router->post('logout', 'Auth\LoginController@logout');
	$router->post('register', 'Auth\RegisterController@register');
	$router->post('password/reset', 'Auth\ForgotPasswordController@sendResetLinkEmail');
	$router->post('password/reset/{token}', 'Auth\ResetPasswordController@reset');

	$router->post('invitation/team/{token}', 'TeamController@invitation');
	$router->post('invitation/application/{token}', 'ApplicationController@invitation');
	$router->post('join/team/{token}', 'TeamController@join');
	$router->post('join/application/{token}', 'ApplicationController@join');

	$router->group(['prefix' => 'user'], function () use ($router) {
		$router->get('/', 'UserController@show');
		$router->put('/', 'UserController@update');
		$router->delete('/', 'UserController@destroy');
		$router->put('/update-email', 'UserController@updateEmail');
		$router->put('/update-password', 'UserController@updatePassword');
	});

	$router->group(['prefix' => 'application'], function () use ($router) {
		$router->get('/', 'ApplicationController@index');
		$router->post('/', 'ApplicationController@store');
		$router->get('{id}', 'ApplicationController@show');
		$router->put('{id}', 'ApplicationController@update');
		$router->delete('{id}', 'ApplicationController@destroy');

		$router->get('{id}/user', 'ApplicationController@getUsers');
		$router->get('{id}/get-token', 'ApplicationController@getInvitationToken');
		$router->post('{id}/invite', 'ApplicationController@inviteUsers');

		$router->put('{application_id}/user/{id}', 'ApplicationController@updateUser');
		$router->delete('{application_id}/user/{id}', 'ApplicationController@deleteUser');

		$router->group(['prefix' => '{application_id}/team'], function () use ($router) {
			$router->get('/', 'TeamController@index');
			$router->post('/', 'TeamController@store');
			$router->get('{id}', 'TeamController@show');
			$router->put('{id}', 'TeamController@update');
			$router->delete('{id}', 'TeamController@destroy');

			$router->get('{id}/user', 'TeamController@getUsers');
			$router->get('{id}/get-token', 'TeamController@getInvitationToken');
			$router->get('{id}/invite', 'TeamController@inviteUsers');

			$router->put('{team_id}/user/{id}', 'TeamController@updateUser');
			$router->delete('{team_id}/user/{id}', 'TeamController@deleteUser');
		});

		$router->group(['prefix' => '{application_id}/form'], function () use ($router) {
			$router->get('/', 'FormController@index');
			$router->post('/', 'FormController@store');
			$router->get('{id}', 'FormController@show');
			$router->put('{id}', 'FormController@update');
			$router->delete('{id}', 'FormController@destroy');
		});
	});

	$router->group(['prefix' => 'form'], function () use ($router) {
		$router->group(['prefix' => '{form_id}/submission'], function () use ($router) {
			$router->get('/', 'SubmissionController@index');
			$router->post('/', 'SubmissionController@store');
			$router->get('{id}', 'SubmissionController@show');
			$router->put('{id}', 'SubmissionController@update');
			$router->delete('{id}', 'SubmissionController@destroy');
		});

		$router->group(['prefix' => '{form_id}/section'], function () use ($router) {
			$router->get('/', 'SectionController@index');
			$router->post('/', 'SectionController@store');
			$router->post('/store', 'SectionController@storeSections');
			$router->put('/update', 'SectionController@updateSections');
			$router->get('{id}', 'SectionController@show');
			$router->post('{id}', 'SectionController@duplicate');
			$router->put('{id}', 'SectionController@update');
			$router->delete('{id}', 'SectionController@destroy');
			$router->post('{id}/move', 'SectionController@move');
		});
	});

	$router->group(['prefix' => 'section'], function () use ($router) {
		$router->group(['prefix' => '{section_id}/question'], function () use ($router) {
			$router->get('/', 'QuestionController@index');
			$router->post('/', 'QuestionController@store');
			$router->get('{id}', 'QuestionController@show');
			$router->post('{id}', 'QuestionController@duplicate');
			$router->put('{id}', 'QuestionController@update');
			$router->delete('{id}', 'QuestionController@destroy');
			$router->post('{id}/move', 'QuestionController@move');
		});
	});

	$router->group(['prefix' => 'question/{question_id}/answer'], function () use ($router) {
		$router->get('/', 'AnswerController@index');
		$router->post('/', 'AnswerController@store');
		$router->delete('/', 'AnswerController@destroyAll');
		$router->delete('/delete', 'AnswerController@destroyFalse');
		$router->get('{id}', 'AnswerController@show');
		$router->put('{id}', 'AnswerController@update');
		$router->delete('{id}', 'AnswerController@destroy');
	});

	$router->group(['prefix' => 'submission/{submission_id}/response'], function () use ($router) {
		$router->get('/', 'ResponseController@index');
		$router->post('/', 'ResponseController@store');
		$router->get('{id}', 'ResponseController@show');
		$router->put('{id}', 'ResponseController@update');
		$router->delete('{id}', 'ResponseController@destroy');
	});

	$router->group(['prefix' => 'question-type'], function () use ($router) {
		$router->get('/', 'QuestionTypeController@index');
		$router->post('/', 'QuestionTypeController@store');
		$router->get('{id}', 'QuestionTypeController@show');
		$router->put('{id}', 'QuestionTypeController@update');
		$router->delete('{id}', 'QuestionTypeController@destroy');
	});

	$router->group(['prefix' => 'role'], function () use ($router) {
		$router->get('/', 'RoleController@index');
		$router->post('/', 'RoleController@store');
		$router->get('{id}', 'RoleController@show');
		$router->put('{id}', 'RoleController@update');
		$router->delete('{id}', 'RoleController@destroy');
	});

	$router->group(['prefix' => 'period'], function () use ($router) {
		$router->get('/', 'PeriodController@index');
		$router->post('/', 'PeriodController@store');
		$router->get('{id}', 'PeriodController@show');
		$router->put('{id}', 'PeriodController@update');
		$router->delete('{id}', 'PeriodController@destroy');
	});
});
