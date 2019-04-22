<?php

namespace App\Providers;

use DB;
use Log;
use Bouncer;
use App\Models\Application;
use App\Models\Answer;
use App\Models\ApplicationUser;
use App\Models\FormTemplate;
use App\Models\Question;
use App\Models\QuestionTrigger;
use App\Models\Response;
use App\Models\Section;
use App\Models\Form;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\Validation;

use App\Observers\Observer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    private $modelList = [
        Application::class      => Observer::class,
        Answer::class           => Observer::class,
        ApplicationUser::class  => Observer::class,
        FormTemplate::class     => Observer::class,
        Question::class         => Observer::class,
        QuestionTrigger::class  => Observer::class,
        Response::class         => Observer::class,
        Section::class          => Observer::class,
        Form::class             => Observer::class,
        Organisation::class     => Observer::class,
        OrganisationUser::class => Observer::class,
        Validation::class       => Observer::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        Relation::morphMap([
            'applications' => 'App\Models\Application',
            'answers' => 'App\Models\Answer',
            'application_users' => 'App\Models\ApplicationUser',
            'form_templates' => 'App\Models\FormTemplate',
            'questions' => 'App\Models\Question',
            'question_triggers' => 'App\Models\QuestionTrigger',
            'responses' => 'App\Models\Response',
            'sections' => 'App\Models\Section',
            'forms' => 'App\Models\Form',
            'organisations' => 'App\Models\Organisation',
            'organisation_users' => 'App\Models\OrganisationUser',
            'validations' => 'App\Models\Validation',
        ]);

        foreach ($this->modelList as $model => $observer) {
            $model::observe($observer);
        }

        // Rename Bouncer tables to prefixed variations due to an overlap with existing roles tables.
        // All four tables are renamed for consistency
        //
        Bouncer::tables([
            'permissions' => 'acl_permissions',
            'assigned_roles' => 'acl_assigned_roles',
            'roles' => 'acl_roles',
            'abilities' => 'acl_abilities',
        ]);
    }
}
