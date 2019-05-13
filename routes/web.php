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

// API Routes
$router->group(['prefix' => 'api'], function () use ($router) {

	// Auth Routes
	$router->post('login', 'Auth\LoginController@login');
	$router->post('logout', 'Auth\LoginController@logout');
	$router->post('register', 'Auth\RegisterController@register');
	$router->post('password/reset', 'Auth\ForgotPasswordController@sendResetLinkEmail');
	$router->post('password/reset/{token}', 'Auth\ResetPasswordController@reset');
	
	// ACL Routes
	$router->get('acl', 'AclController@index');
	$router->get('acl/{resource}/{id}', 'AclController@show');
	$router->put('acl/{resource}/{id}', 'AclController@update');

	// Access Settings Routes
	$router->get('access-settings/{resource}/{id}', 'AccessSettingsController@show');
	$router->put('access-settings/{resource}/{id}', 'AccessSettingsController@update');

	// Invitation Routes
	$router->post('join/organisation/{token}', 'OrganisationController@join');
	$router->post('join/application/{token}', 'ApplicationController@join');

	// File Routes
	$router->group(['prefix' => 'file'], function () use ($router) {
		$router->post('/', 'FileController@store');
		$router->delete('/', 'FileController@destroy');
		$router->get('/{url}/', 'FileController@download');
	});

	// User Routes
	$router->group(['prefix' => 'user'], function () use ($router) {
		$router->get('/', 'UserController@show');
		$router->put('/', 'UserController@update');
		$router->delete('/', 'UserController@destroy');
		$router->put('update-email', 'UserController@updateEmail');
		$router->put('update-password', 'UserController@updatePassword');
	});

	// Applications Routes
	$router->group(['prefix' => 'application'], function () use ($router) {
		$router->get('/', 'ApplicationController@index');
		$router->post('/', 'ApplicationController@store');

		// Application Routes
		$router->group(['prefix' => '{application_slug}'], function () use ($router) {
			$router->get('/', 'ApplicationController@show');
			$router->put('/', 'ApplicationController@update');
			$router->delete('/', 'ApplicationController@destroy');
            $router->get('export', 'ApplicationController@exportCSV');
            $router->get('section', 'SectionController@all');

			// Application User Routes
			$router->group(['prefix' => 'user'], function () use ($router) {
				$router->get('/', 'ApplicationController@getUsers');
				$router->put('{id}', 'ApplicationController@updateUser');
				$router->delete('{id}', 'ApplicationController@deleteUser');
			});

			// Application Invitation Routes
			$router->group(['prefix' => 'invited'], function () use ($router) {
				$router->put('{id}', 'ApplicationController@updateInvitedUser');
				$router->delete('{id}', 'ApplicationController@deleteInvitedUser');
			});

			// $router->post('invite', 'ApplicationController@inviteUsers');
			$router->post('invite', 'ApplicationInvitationController@store');

			// Application Organisations Routes
			$router->group(['prefix' => 'organisation'], function () use ($router) {
				$router->get('/', 'OrganisationController@index');
				$router->post('/', 'OrganisationController@store');
                $router->get('/user', 'OrganisationController@getUsers');

				// Application Organisation Routes
				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'OrganisationController@show');
					$router->put('/', 'OrganisationController@update');
					$router->delete('/', 'OrganisationController@destroy');
					// $router->post('invite', 'OrganisationController@inviteUsers');
                    $router->post('invite', 'OrganisationInvitationController@store');

					// Application User Routes
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

			// Application Form Templates Routes
			$router->group(['prefix' => 'form-templates'], function () use ($router) {
				$router->get('/', 'FormTemplateController@index');
				$router->post('/', 'FormTemplateController@store');
				$router->post('auto', 'FormTemplateController@setAuto');

				// Application Form Template Routes
				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'FormTemplateController@show');
					$router->post('/', 'FormTemplateController@update');
                    $router->post('/duplicate', 'FormTemplateController@duplicate');
					$router->delete('/', 'FormTemplateController@destroy');
					$router->get('export', 'FormTemplateFileController@show');
					$router->post('/upload', 'FormTemplateUploadController@store');
					$router->post('/form/upload', 'FormUploadController@store');
				});
			});

			// Application Form Routes
            $router->group(['prefix' => 'form'], function () use ($router) {
                $router->get('/', 'FormController@all');
                $router->post('/filter', 'ApplicationController@filterForm');
				$router->get('{id}', 'FormController@one');
				$router->post('/upload', 'ApplicationFormUploadController@store');
            });

			$router->group(['prefix' => 'application-email'], function () use ($router) {
				$router->get('/', 'ApplicationEmailController@index');

				$router->group(['prefix' => '{id}'], function () use ($router) {
					$router->get('/', 'ApplicationEmailController@show');
				});
			});

            $router->group(['prefix' => 'notes'], function () use ($router) {
                $router->get('/', 'NoteController@index');
                $router->post('/', 'NoteController@store');

                $router->group(['prefix' => '{id}'], function () use ($router) {
                    $router->get('/', 'NoteController@show');
                    $router->post('/', 'NoteController@update');
                    $router->delete('/', 'NoteController@destroy');
                });
            });
		});
	});

	// Form Template Routes
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

		// Form Template Section Routes
		$router->group(['prefix' => 'section'], function () use ($router) {
			$router->get('/', 'SectionController@index');
			$router->post('/', 'SectionController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'SectionController@show');
				$router->put('/', 'SectionController@update');
				$router->delete('/', 'SectionController@destroy');
				$router->post('move', 'SectionController@move');
			});
		});

		// Form Template Validation Routes
		$router->group(['prefix' => 'validation'], function () use ($router) {
			$router->get('/', 'ValidationController@index');
			$router->post('/', 'ValidationController@store');

			$router->group(['prefix' => '{id}'], function () use ($router) {
				$router->get('/', 'ValidationController@show');
				$router->put('/', 'ValidationController@update');
				$router->delete('/', 'ValidationController@destroy');
			});
		});

		// Form Template Trigger Routes
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

	// Section Routes
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

	// Question Routes
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

	// Form Routes
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

	// Question Type Routes
	$router->group(['prefix' => 'question-type'], function () use ($router) {
		$router->get('/', 'QuestionTypeController@index');
		$router->post('/', 'QuestionTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'QuestionTypeController@show');
		});
	});

	// Role Routes
	$router->group(['prefix' => 'role'], function () use ($router) {
		$router->get('/', 'RoleController@index');
		$router->post('/', 'RoleController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'RoleController@show');
		});
	});

	// Type Routes
    $router->group(['prefix' => 'type'], function () use ($router) {
        $router->get('/', 'TypeController@index');
        $router->post('/', 'TypeController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'TypeController@show');
        });
    });

	// Country Routes
    $router->group(['prefix' => 'country'], function () use ($router) {
        $router->get('/', 'CountryController@index');
        $router->post('/', 'CountryController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'CountryController@show');
        });
    });

	// Period Routes
	$router->group(['prefix' => 'period'], function () use ($router) {
		$router->get('/', 'PeriodController@index');
		$router->post('/', 'PeriodController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'PeriodController@show');
		});
	});

	// Metta Routes
	$router->group(['prefix' => 'meta'], function () use ($router) {
        $router->get('/', 'MetaController@index');
        $router->post('/', 'MetaController@store');

        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('/', 'MetaController@show');
            $router->put('/', 'MetaController@update');
            $router->delete('/', 'MetaController@destroy');
        });
    });

	// Validation Routes
	$router->group(['prefix' => 'validation-type'], function () use ($router) {
		$router->get('/', 'ValidationTypeController@index');
		$router->post('/', 'ValidationTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ValidationTypeController@show');
		});
	});

	// Status Routes
	$router->group(['prefix' => 'status'], function () use ($router) {
		$router->get('/', 'StatusController@index');
		$router->post('/', 'StatusController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'StatusController@show');
		});
	});

	// Comparator Routes
	$router->group(['prefix' => 'comparator'], function () use ($router) {
		$router->get('/', 'ComparatorController@index');
		$router->post('/', 'ComparatorController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ComparatorController@show');
		});
	});

	// Action Type Routes
	$router->group(['prefix' => 'action-type'], function () use ($router) {
		$router->get('/', 'ActionTypeController@index');
		$router->post('/', 'ActionTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'ActionTypeController@show');
		});
	});

	// Trigger Type Routes
	$router->group(['prefix' => 'trigger-type'], function () use ($router) {
		$router->get('/', 'TriggerTypeController@index');
		$router->post('/', 'TriggerTypeController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'TriggerTypeController@show');
		});
	});

	// Answer Sort Routes
	$router->group(['prefix' => 'answer-sort'], function () use ($router) {
		$router->get('/', 'AnswerSortController@index');
		$router->post('/', 'AnswerSortController@store');

		$router->group(['prefix' => '{id}'], function () use ($router) {
			$router->get('/', 'AnswerSortController@show');
		});
	});

	// Short URL Routes
    $router->group(['prefix' => 'short-url'], function () use ($router) {
        $router->get('/', 'ShortUrlController@index');
        $router->post('/', 'ShortUrlController@store');
        $router->get('{short_url}', 'ShortUrlController@show');
    });

    // Workflow Routes
    $router->get('application/{application_slug}/workflow', 'WorkflowController@index');
    $router->get('application/{application_slug}/workflow/{id}', 'WorkflowController@show');
    $router->post('application/{application_slug}/workflow', 'WorkflowController@store');
    $router->delete('application/{application_slug}/workflow/{id}', 'WorkflowController@destroy');
});
