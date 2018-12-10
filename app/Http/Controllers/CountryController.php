<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Country;
use App\Http\Resources\CountryResource;
use Illuminate\Http\Request;

class CountryController extends Controller
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
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$countries = Country::all();
		return $this->returnSuccessMessage('countries', CountryResource::collection($countries));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('country', 403, 'create');
			}

			// Create Country
			$country = Country::create($request->only('name'));

			if ($country) {
				return $this->returnSuccessMessage('country', new CountryResource($country));
			}

			// Send error if country is not created
			return $this->returnError('country', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
        $country = Country::find($id);
		if ($country) {
			return $this->returnSuccessMessage('country', new CountryResource($country));
		}

		// Send error if country does not exist
		return $this->returnError('country', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('country', 403, 'update');
			}

            $country = Country::find($id);

			// Send error if country does not exist
			if (!$country) {
				return $this->returnError('country', 404, 'update');
			}

			// Update country
			if ($country->fill($request->only('name'))->save()) {
				return $this->returnSuccessMessage('country', new CountryResource($country));
			}

			// Send error if there is an error on update
			return $this->returnError('country', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		try {
			if (!$this->hasPermission()) {
				return $this->returnError('country', 403, 'delete');
			}

            $country = Country::find($id);

			// Send error if country does not exist
			if (!$country) {
				return $this->returnError('country', 404, 'delete');
			}

			// Delete country
			if ($country->delete()) {
				return $this->returnSuccessMessage('message', 'Country has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('country', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Check whether user is Super Admin or not
	 *
	 * @return bool
	 */
	protected function hasPermission()
	{
		$user = Auth::user();
		if ($user->type->name != 'Super Admin') {
			return false;
		}

		return true;
	}
}
