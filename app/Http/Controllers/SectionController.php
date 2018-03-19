<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Form;
use App\Http\Resources\SectionResource;
use Illuminate\Http\Request;

class SectionController extends Controller
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
	 * @param  int $form_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_id)
	{
		$sections = Form::find($form_id)->sections()->get();

		return $this->returnSuccessMessage('sections', SectionResource::collection($sections));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'name'       => 'required|max:191',
			'section_id' => 'nullable|integer|min:1',
			'order'      => 'required|integer|min:0'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create section');
			}

			// Create section
			$section = $form->sections()->create($request->only('name', 'section_id', 'order'));

			if ($section) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if section is not created
			return $this->returnError('section', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($form_id, $id)
	{
		$form = Form::find($form_id);

		// Send error if form does not exist
		if (!$form) {
			return $this->returnError('form', 404, 'show section');
		}

		$section = $form->sections()->where('id', $id)->first();
		if ($section) {
			return $this->returnSuccessMessage('section', new SectionResource($section));
		}

		// Send error if section does not exist
		return $this->returnError('section', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($form_id, Request $request, $id)
	{
		$this->validate($request, [
			'name'       => 'filled|max:191',
			'section_id' => 'nullable|integer|min:1',
			'order'      => 'filled|integer|min:0'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update section');
			}

			$section = $form->sections()->where('id', $id)->first();

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update');
			}

			// Update section
			if ($section->fill($request->only('name', 'section_id', 'order'))->save()) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if there is an error on update
			return $this->returnError('section', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($form_id, $id)
	{
		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'delete section');
			}

			$section = $form->sections()->where('id', $id)->first();

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'delete');
			}

			if ($section->delete()) {
				return $this->returnSuccessMessage('message', 'Section has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('section', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}
}
