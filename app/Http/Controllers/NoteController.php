<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Note;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Http\Resources\NoteResource;
use Illuminate\Http\Request;

class NoteController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth:api');
	}

	/**
	 * Display a listing of the resource.
	 *
     * @param  string $application_slug
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_slug)
	{

        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

        // Check whether user has permission
        if (!$this->hasPermission($user, $application)) {
            return $this->returnError('application', 403, 'delete form_templates');
        }

        // Send error if application does not exist
        if (!$application) {
            return $this->returnApplicationNameError();
        }

        $notes = $application->notes;
		return $this->returnSuccessMessage('notes', NoteResource::collection($notes));
	}

	/**
	 * Store a newly created resource in storage.
	 *
     * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($application_slug, Request $request)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

            // Check whether user has permission
            if (!$this->hasPermission($user, $application)) {
                return $this->returnError('application', 403, 'delete form_templates');
            }

            // Send error if application does not exist
            if (!$application) {
                return $this->returnApplicationNameError();
            }

            $note = $application->notes()->create([
                'event' => $request->input('event'),
                'note' => $request->input('note'),
                'user_id' => $user->id,
                'recordable_id' => $request->input('recordable_id'),
                'recordable_type' => $request->input('recordable_type')
            ]);

			if ($note) {
				return $this->returnSuccessMessage('note', new NoteResource($note));
			}

			// Send error if note is not created
			return $this->returnError('note', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
     * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug, $id)
	{
        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

        // Check whether user has permission
        if (!$this->hasPermission($user, $application)) {
            return $this->returnError('application', 403, 'delete form_templates');
        }

        // Send error if application does not exist
        if (!$application) {
            return $this->returnApplicationNameError();
        }

        $note = $application->notes()->find($id);
		if ($note) {
			return $this->returnSuccessMessage('note', new NoteResource($note));
		}

		// Send error if note does not exist
		return $this->returnError('note', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
     * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($application_slug, $id, Request $request)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

            // Check whether user has permission
            if (!$this->hasPermission($user, $application)) {
                return $this->returnError('application', 403, 'delete form_templates');
            }

            // Send error if application does not exist
            if (!$application) {
                return $this->returnApplicationNameError();
            }

            $note = $application->notes()->find($id);

			// Send error if note does not exist
			if (!$note) {
				return $this->returnError('note', 404, 'update');
			}

			// Update note
			if ($note->fill($request->only('event', 'note', 'recordable_id', 'recordable_type'))->save()) {
				return $this->returnSuccessMessage('note', new NoteResource($note));
			}

			// Send error if there is an error on update
			return $this->returnError('note', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
     * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug, $id)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

            // Check whether user has permission
            if (!$this->hasPermission($user, $application)) {
                return $this->returnError('application', 403, 'delete form_templates');
            }

            // Send error if application does not exist
            if (!$application) {
                return $this->returnApplicationNameError();
            }

            $note = $application->notes()->find($id);

			// Send error if note does not exist
			if (!$note) {
				return $this->returnError('note', 404, 'delete');
			}

			// Delete note
			if ($note->delete()) {
				return $this->returnSuccessMessage('message', 'Note has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('note', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

    /**
     * Check whether user has permission or not
     *
     * @param  $user
     * @param  $application
     *
     * @return bool
     */
    protected function hasPermission($user, $application)
    {
        if ($user->role->name == 'Super Admin') {
            return true;
        }

        $role = ApplicationUser::where([
            'user_id' => $user->id,
            'application_id' => $application->id
        ])->first()->role;

        if ($role->name != 'Admin') {
            return false;
        }

        return true;
    }
}
