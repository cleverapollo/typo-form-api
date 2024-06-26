<?php

namespace App\Observers;

use Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Log;
use App\Models\Note;

class Observer
{
    /**
     * Create Log for Model Action
     *
     * @param Model $model
     * @param String $method
     * @return void
     */
    private function createLog($model, $method) {
        $request = app('Illuminate\Http\Request');
        $ip_address = $request->ip();
        $user_id = Auth::user() ? Auth::user()->id : null;
        $action = $this->getEventType($method);
        $resourcable_type = class_basename($model);
        $resourcable_id = $model->id;

        switch ($resourcable_type) {
            case 'FormTemplate':
            case 'Organisation':
            case 'ApplicationUser':
                $application_id = $model->application_id;
                break;
            case 'Section':
            case 'Form':
            case 'QuestionTrigger':
            case 'Validation':
                $application_id = $model->form_template->application_id;
                break;
            case 'Question':
                $application_id = $model->section->form_template->application_id;
                break;
            case 'Answer':
            case 'Response':
                $application_id = $model->question->section->form_template->application_id;
                break;
            case 'OrganisationUser':
                $application_id = $model->organisation->application_id;
                break;
            default:
                $application_id = $model->id;
        }

        Log::create([
            'user_id'           => $user_id,
            'application_id'    => $application_id,
            'action'            => $action,
            'resourcable_id'    => $resourcable_id,
            'resourcable_type'  => $resourcable_type,
            'ip_address'        => $ip_address
        ]);
    }

    /**
     * Handle to the Model action type.
     *
     * @param String $method
     * @return string
     */
    private function getEventType($method) {
        return explode('::', $method)[1];
    }

    /**
     * Handle to the Model "created" event.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model)
    {
        // $this->createLog($model, __METHOD__);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  Model $model
     * @return void
     */
    public function updating(Model $model)
    {
        // $this->createLog($model, __METHOD__);
    }

    /**
     * Handle the User "deleting" event.
     *
     * @param  Model $model
     * @return void
     */
    public function deleting(Model $model)
    {
        // $this->createLog($model, __METHOD__);
    }
}