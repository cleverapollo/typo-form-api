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

$router->post('auth/signin', 'Auth\OAuth2Controller@signin');
$router->post('auth/{provider}', 'Auth\OAuth2Controller@handleProviderCallback');

$router->group(['prefix' => 'api'], function () use ($router) {
	$router->post('login', 'Auth\LoginController@login');
	$router->post('logout', 'Auth\LoginController@logout');
	$router->post('register', 'Auth\RegisterController@register');
	$router->post('password/reset', 'Auth\ForgotPasswordController@sendResetLinkEmail');
	$router->post('password/reset/{token}', 'Auth\ResetPasswordController@reset');

	$router->post('join/organisation/{token}', 'OrganisationController@join');
	$router->post('join/application/{token}', 'ApplicationController@join');

	$router->group(['prefix' => 'file'], function () use ($router) {
		$router->post('/', 'FileController@store');
		$router->delete('/', 'FileController@destroy');
		$router->get('/{url}/', 'FileController@download');
	});

	$router->group(['prefix' => 'user'], function () use ($router) {
		$router->get('/', 'UserController@show');
		$router->put('/', 'UserController@update');
		$router->delete('/', 'UserController@destroy');
		$router->put('update-email', 'UserController@updateEmail');
		$router->put('update-password', 'UserController@updatePassword');
	});

	$router->group(['prefix' => 'application'], function () use ($router) {
		$router->get('/', 'ApplicationController@index');
		$router->post('/', 'ApplicationController@store');

		$router->group(['prefix' => '{application_slug}'], function () use ($router) {
			$router->get('/', 'ApplicationController@show');
			$router->put('/', 'ApplicationController@update');
			$router->delete('/', 'ApplicationController@destroy');
            $router->get('export', 'ApplicationController@exportCSV');
            $router->get('section', 'SectionController@all');

			$router->group(['prefix' => 'user'], function () use ($router) {
				$router->get('/', 'ApplicationController@getUsers');
				$router->put('{id}', 'ApplicationController@updateUser');
				$router->delete('{id}', 'ApplicationController@deleteUser');
			});

			$router->group(['prefix' => 'invited'], function () use ($router) {
				$router->put('{id}', 'ApplicationController@updateInvitedUser');
				$router->delete('{id}', 'ApplicationController@deleteInvitedUser');
			});

			$router->post('invite', 'ApplicationController@inviteUsers');

			$router->group(['prefix' => 'organisation'], function () use ($router) {
				$router->get('/', 'OrganisationController@index');
				$router->post('/', 'OrganisationController@store');
                $router->get('/user', 'OrganisationController@getUsers');

				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'OrganisationController@show');
					$router->put('/', 'OrganisationController@update');
					$router->delete('/', 'OrganisationController@destroy');
					$router->post('invite', 'OrganisationController@inviteUsers');

					$router->group(['prefix' => 'user'], function () use ($router) {
						$router->get('/', 'OrganisationController@getOrganisationUsers');
						$router->put('{user_id}', 'OrganisationController@updateUser');
						$router->delete('{user_id}', 'OrganisationController@deleteUser');
					});

					$router->group(['prefix' => 'invited'], function () use ($router) {
						$router->put('{invited_id}', 'OrganisationController@updateInvitedUser');
						$router->delete('{invited_id}', 'OrganisationController@deleteInvitedUser');
					});
				});
			});

			$router->group(['prefix' => 'form-templates'], function () use ($router) {
				$router->get('/', 'FormTemplateController@index');
				$router->post('/', 'FormTemplateController@store');
				$router->post('auto', 'FormTemplateController@setAuto');

				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'FormTemplateController@show');
					$router->post('/', 'FormTemplateController@update');
                    $router->post('/duplicate', 'FormTemplateController@duplicate');
					$router->delete('/', 'FormTemplateController@destroy');
					$router->get('export', 'FormTemplateController@exportCSV');
					$router->post('/upload', 'FormTemplateController@uploadFormTemplate');
					$router->post('/form/upload', 'FormUploadController@store');
				});
			});

            $router->group(['prefix' => 'form'], function () use ($router) {
                $router->get('/', 'FormController@all');
                $router->post('/filter', 'ApplicationController@filterForm');
                $router->post('/filter/export', 'ApplicationController@exportForm');
                $router->get('{id}', 'FormController@one');
            });

			$router->group(['prefix' => 'application-email'], function () use ($router) {
				$router->get('/', 'ApplicationEmailController@index');
				// $router->post('/', 'ApplicationEmailController@store');

				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'ApplicationEmailController@show');
					// $router->put('/', 'ApplicationEmailController@update');
					// $router->delete('/', 'ApplicationEmailController@destroy');
				});
			});
		});
	});

	$router->group(['prefix' => 'form-templates/{form_template_id}'], function () use ($router) {
		$router->group(['prefix' => 'form'], function () use ($router) {
			$router->get('/', 'FormController@index');
			$router->post('/', 'FormController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'FormController@show');
				$router->put('/', 'FormController@update');
                $router->post('/', 'FormController@duplicate');
				$router->delete('/', 'FormController@destroy');
				$router->get('data', 'FormController@getData');
			});
		});

		$router->group(['prefix' => 'section'], function () use ($router) {
			$router->get('/', 'SectionController@index');
			$router->post('/', 'SectionController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'SectionController@show');
				$router->put('/', 'SectionController@update');
				$router->delete('/', 'SectionController@destroy');
				// $router->post('/', 'SectionController@duplicate');
				$router->post('move', 'SectionController@move');
			});
		});

		$router->group(['prefix' => 'validation'], function () use ($router) {
			$router->get('/', 'ValidationController@index');
			$router->post('/', 'ValidationController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'ValidationController@show');
				$router->put('/', 'ValidationController@update');
				$router->delete('/', 'ValidationController@destroy');
			});
		});

		$router->group(['prefix' => 'trigger'], function () use ($router) {
			$router->get('/', 'QuestionTriggerController@index');
			$router->post('/', 'QuestionTriggerController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'QuestionTriggerController@show');
				$router->put('/', 'QuestionTriggerController@update');
				$router->delete('/', 'QuestionTriggerController@destroy');
			});
		});
	});

	$router->group(['prefix' => 'section/{section_id}/question'], function () use ($router) {
		$router->get('/', 'QuestionController@index');
		$router->post('/', 'QuestionController@store');
		$router->delete('/', 'QuestionController@destroyAll');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'QuestionController@show');
			$router->put('/', 'QuestionController@update');
			$router->delete('/', 'QuestionController@destroy');
			$router->post('/', 'QuestionController@duplicate');
			$router->post('move', 'QuestionController@move');
		});
	});

	$router->group(['prefix' => 'question/{question_id}/answer'], function () use ($router) {
		$router->get('/', 'AnswerController@index');
		$router->post('/', 'AnswerController@store');
		$router->delete('/', 'AnswerController@destroyAll');
		$router->delete('delete', 'AnswerController@destroyFalse');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'AnswerController@show');
			$router->put('/', 'AnswerController@update');
			$router->delete('/', 'AnswerController@destroy');
			$router->post('move', 'AnswerController@move');
		});
	});

	$router->group(['prefix' => 'form/{form_id}/response'], function () use ($router) {
		$router->get('/', 'ResponseController@index');
		$router->post('/', 'ResponseController@store');
		$router->delete('/section/{section_id}/{order}', 'ResponseController@deleteSectionResponse');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ResponseController@show');
			$router->put('/', 'ResponseController@update');
			$router->delete('/', 'ResponseController@destroy');
		});
	});

	$router->group(['prefix' => 'question-type'], function () use ($router) {
		$router->get('/', 'QuestionTypeController@index');
		$router->post('/', 'QuestionTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'QuestionTypeController@show');
			// $router->put('/', 'QuestionTypeController@update');
			// $router->delete('/', 'QuestionTypeController@destroy');
		});
	});

	$router->group(['prefix' => 'role'], function () use ($router) {
		$router->get('/', 'RoleController@index');
		$router->post('/', 'RoleController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'RoleController@show');
			// $router->put('/', 'RoleController@update');
			// $router->delete('/', 'RoleController@destroy');
		});
	});

    $router->group(['prefix' => 'type'], function () use ($router) {
        $router->get('/', 'TypeController@index');
        $router->post('/', 'TypeController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'TypeController@show');
            // $router->put('/', 'TypeController@update');
            // $router->delete('/', 'TypeController@destroy');
        });
    });

    $router->group(['prefix' => 'country'], function () use ($router) {
        $router->get('/', 'CountryController@index');
        $router->post('/', 'CountryController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'CountryController@show');
            // $router->put('/', 'CountryController@update');
            // $router->delete('/', 'CountryController@destroy');
        });
    });

	$router->group(['prefix' => 'period'], function () use ($router) {
		$router->get('/', 'PeriodController@index');
		$router->post('/', 'PeriodController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'PeriodController@show');
			// $router->put('/', 'PeriodController@update');
			// $router->delete('/', 'PeriodController@destroy');
		});
	});

	$router->group(['prefix' => 'meta'], function () use ($router) {
        $router->get('/', 'MetaController@index');
        $router->post('/', 'MetaController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'MetaController@show');
            $router->put('/', 'MetaController@update');
            $router->delete('/', 'MetaController@destroy');
        });
    });

	$router->group(['prefix' => 'validation-type'], function () use ($router) {
		$router->get('/', 'ValidationTypeController@index');
		$router->post('/', 'ValidationTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ValidationTypeController@show');
			// $router->put('/', 'ValidationTypeController@update');
			// $router->delete('/', 'ValidationTypeController@destroy');
		});
	});

	$router->group(['prefix' => 'status'], function () use ($router) {
		$router->get('/', 'StatusController@index');
		$router->post('/', 'StatusController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'StatusController@show');
			// $router->put('/', 'StatusController@update');
			// $router->delete('/', 'StatusController@destroy');
		});
	});

	$router->group(['prefix' => 'comparator'], function () use ($router) {
		$router->get('/', 'ComparatorController@index');
		$router->post('/', 'ComparatorController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ComparatorController@show');
			// $router->put('/', 'ComparatorController@update');
			// $router->delete('/', 'ComparatorController@destroy');
		});
	});

	$router->group(['prefix' => 'action-type'], function () use ($router) {
		$router->get('/', 'ActionTypeController@index');
		$router->post('/', 'ActionTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ActionTypeController@show');
			// $router->put('/', 'ActionTypeController@update');
			// $router->delete('/', 'ActionTypeController@destroy');
		});
	});

	$router->group(['prefix' => 'trigger-type'], function () use ($router) {
		$router->get('/', 'TriggerTypeController@index');
		$router->post('/', 'TriggerTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'TriggerTypeController@show');
			// $router->put('/', 'TriggerTypeController@update');
			// $router->delete('/', 'TriggerTypeController@destroy');
		});
	});

	$router->group(['prefix' => 'answer-sort'], function () use ($router) {
		$router->get('/', 'AnswerSortController@index');
		$router->post('/', 'AnswerSortController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'AnswerSortController@show');
			// $router->put('/', 'AnswerSortController@update');
			// $router->delete('/', 'AnswerSortController@destroy');
		});
	});

    $router->group(['prefix' => 'short-url'], function () use ($router) {
        $router->get('/', 'ShortUrlController@index');
        $router->post('/', 'ShortUrlController@store');
        $router->get('{short_url}', 'ShortUrlController@show');
    });
});
