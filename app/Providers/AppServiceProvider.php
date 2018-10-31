<?php

namespace App\Providers;

use DB;
use Log;
use App\Models\Answer;
use App\Models\ApplicationUser;
use App\Models\Form;
use App\Models\Question;
use App\Models\QuestionTrigger;
use App\Models\Response;
use App\Models\Section;
use App\Models\Submission;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Validation;

use App\Observers\Observer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    private $modelList = [
        Answer::class           => Observer::class,
        ApplicationUser::class  => Observer::class,
        Form::class             => Observer::class,
        Question::class         => Observer::class,
        QuestionTrigger::class  => Observer::class,
        Response::class         => Observer::class,
        Section::class          => Observer::class,
        Submission::class       => Observer::class,
        Team::class             => Observer::class,
        TeamUser::class         => Observer::class,
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
            'answers' => 'App\Models\Answer',
            'application_users' => 'App\Models\ApplicationUser',
            'forms' => 'App\Models\Form',
            'questions' => 'App\Models\Question',
            'question_triggers' => 'App\Models\QuestionTrigger',
            'responses' => 'App\Models\Response',
            'sections' => 'App\Models\Section',
            'submissions' => 'App\Models\Submission',
            'teams' => 'App\Models\Team',
            'team_users' => 'App\Models\TeamUser',
            'validations' => 'App\Models\Validation',
        ]);

        foreach ($this->modelList as $model => $observer) {
            $model::observe($observer);
        }

        DB::listen(function($query) {
            Log::info(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });
    }
}
