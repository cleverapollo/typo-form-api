<?php

namespace App\Http\Controllers;

use \ApplicationUserRepository;
use App\User;
use App\Http\Resources\ApplicationUserResource;
use App\Jobs\UsersNotification;
use Auth;
use Illuminate\Http\Request;

class ApplicationUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('resolve-application-slug');
        $this->middleware('application-admin');
    }
    
    /**
     * Get users for the Application.
     *
     * @param  Request $request - contains application from middleware resolver
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $application = $request->get('application');
        $applicationUsers = ApplicationUserRepository::users($application->id);
        return ApplicationUserResource::collection($applicationUsers);
    }

    /**
     * Update user role in the Application.
     *
     * @param  string $application_slug
     * @param  int $id
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update($application_slug, $id, Request $request)
    {
        $input = $this->validate($request, [
            'application_role_id' => 'required|integer|min:2|exists:roles,id',
            'period' => 'required|numeric',
            'multiplier' => 'required|numeric',
        ]);

        $application = $request->get('application');
        $applicationUser = ApplicationUserRepository::findOrFail($application->id, $id);

        $multiplier = intval($request->input('multiplier'));
        $period = intval($request->input('period'));
        $meta = array_merge($applicationUser->meta ?? [], compact('multiplier', 'period'));

        $applicationUser->update([
            'role_id' => $input['application_role_id'],
            'meta' => $meta,
        ]);
        
        $user = User::findOrFail($id)->update([
            'workflow_delay' => $multiplier * $period,
        ]);

        // TODO translation string?
        // TODO should be an event? doesn't really need to be part of main flow..
        dispatch(new UsersNotification([
            'users' => [$applicationUser],
            'message' => 'Application user role has been updated successfully.'
        ]));

        return new ApplicationUserResource($applicationUser);
    }

    /**
     * Delete user from the Application.
     *
     * @param  string $application_slug
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $application_slug, $id)
    {
        $application = $request->get('application');
        $applicationUser = ApplicationUserRepository::findOrFail($application->id, $id);
        $applicationUser->delete();

        return response()->json(['message' => __('responses.application_user_delete_200')], 200);
    }
}
