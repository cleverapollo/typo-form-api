<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Http\Resources\GroupResource;
use Illuminate\Http\Request;

class GroupController extends Controller
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
	 * @param  int $section_id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($section_id)
	{
		$groups = Section::find($section_id)->groups()->get();
		return $this->returnSuccessMessage('groups', GroupResource::collection($groups));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $section_id
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($section_id, Request $request)
	{
		$section = Section::find($section_id);

		// Send error if section does not exist
		if (!$section) {
			return $this->returnErrorMessage('section', 404, 'create group');
		}

		// Create group
		$group = $section->groups()->create($request->only('name', 'repeatable'));

		if ($group) {
			return $this->returnSuccessMessage('group', new GroupResource($group));
		}

		// Send error if group is not created
		return $this->returnErrorMessage('group', 503, 'create');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $section_id
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($section_id, $id)
	{
		$section = Section::find($section_id);

		// Send error if section does not exist
		if (!$section) {
			return $this->returnErrorMessage('section', 404, 'show group');
		}

		$group = $section->groups()->where('id', $id)->first();
		if ($group) {
			return $this->returnSuccessMessage('group', new GroupResource($group));
		}

		// Send error if group does not exist
		return $this->returnErrorMessage('group', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $section_id
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($section_id, Request $request, $id)
	{
		$section = Section::find($section_id);

		// Send error if section does not exist
		if (!$section) {
			return $this->returnErrorMessage('section', 404, 'update group');
		}

		$group = $section->groups()->where('id', $id)->first();

		// Send error if group does not exist
		if (!$group) {
			return $this->returnErrorMessage('group', 404, 'update');
		}

		// Update group
		if ($group->fill($request->only('name', 'repeatable'))->save()) {
			return $this->returnSuccessMessage('group', new GroupResource($group));
		}

		// Send error if there is an error on update
		return $this->returnErrorMessage('group', 503, 'update');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $section_id
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($section_id, $id)
	{
		$section = Section::find($section_id);

		// Send error if section does not exist
		if (!$section) {
			return $this->returnErrorMessage('section', 404, 'delete group');
		}

		$group = $section->groups()->where('id', $id)->first();

		// Send error if group does not exist
		if (!$group) {
			return $this->returnErrorMessage('group', 404, 'delete');
		}

		if ($group->delete()) {
			return $this->returnSuccessMessage('message', 'Group has been deleted successfully.');
		}

		// Send error if there is an error on update
		return $this->returnErrorMessage('group', 503, 'delete');
	}
}
