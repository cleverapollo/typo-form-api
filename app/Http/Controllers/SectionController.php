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
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'parent_section_id' => 'nullable|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create section');
			}

			// Count order
			$order = 1;
			$parent_section_id = $request->input('parent_section_id', null);
			if (!$parent_section_id) {
				if (count($form->sections) > 0) {
					$order = $form->sections()->where('parent_section_id', null)->max('order') + 1;
				}
			} else {
				$parent_section = $form->sections()->find($parent_section_id);

				// Send error if parent section does not exist
				if (!$parent_section) {
					return $this->returnError('parent section', 404, 'create section');
				}

				if (count($parent_section->children) > 0) {
					$order = $parent_section->children()->max('order') + 1;
				}

				if (count($parent_section->questions) > 0) {
					$order = max($order, $parent_section->questions()->max('order') + 1);
				}
			}

			// Create section
			$section = $form->sections()->create([
				'name' => $request->input('name'),
				'parent_section_id' => $parent_section_id,
				'order' => $order,
				'repeatable' => $request->input('repeatable', 0),
				'max_rows' => $request->input('max_rows', null),
				'min_rows' => $request->input('min_rows', null)
			]);

			if ($section) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if section is not created
			return $this->returnError('section', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Duplicate a resource in storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function duplicate($form_id, $id)
	{
		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'duplicate section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'duplicate');
			}

			// Duplicate section
			$newSection = $form->sections()->create([
				'name' => $section->name,
				'parent_section_id' => $section->parent_section_id,
				'order' => ($section->order + 1),
				'repeatable' => $section->repeatable,
				'max_rows' => $section->max_rows,
				'min_rows' => $section->min_rows
			]);

			if ($newSection) {
				// Update other sections order
				$form->sections()->where([
					['id', '<>', $newSection->id],
					['parent_section_id', '=', $newSection->parent_section_id],
					['order', '>=', $newSection->order]
				])->get()->each(function ($other) {
					$other->order += 1;
					$other->save();
				});

				// Update other questions order
				if ($newSection->parent) {
					$newSection->parent->questions()->where('order', '>=', $newSection->order)->get()->each(function ($other) {
						$other->order += 1;
						$other->save();
					});
				}

				return $this->returnSuccessMessage('section', new SectionResource($newSection));
			}

			// Send error if section is not duplicated
			return $this->returnError('section', 503, 'duplicate');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
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

		$section = $form->sections()->find($id);
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
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update');
			}

			// Update section
			if ($section->fill($request->only('name', 'repeatable', 'max_rows', 'min_rows'))->save()) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if there is an error on update
			return $this->returnError('section', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
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

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'delete');
			}

			if ($section->delete()) {
				return $this->returnSuccessMessage('message', 'Section has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('section', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Move the specified resource from storage.
	 *
	 * @param $form_id
	 * @param $id
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function move($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'parent_section_id' => 'nullable|integer|min:1',
			'order' => 'required|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'move section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'move');
			}

			$parent_section_id = $request->input('parent_section_id', null);
			if ($parent_section_id) {
				$parent_section = $form->sections()->find($parent_section_id);

				// Send error if parent section does not exist
				if (!$parent_section) {
					return $this->returnError('parent section', 404, 'move section');
				}
			}

			// Move section
			$section->parent_section_id = $parent_section_id;
			$section->order = $request->input('order');
			$section->save();

			// Update other sections order
			$form->sections()->where([
				['id', '<>', $section->id],
				['parent_section_id', '=', $parent_section_id],
				['order', '>=', $section->order]
			])->get()->each(function ($other) {
				$other->order += 1;
				$other->save();
			});

			// Update other questions order
			if ($parent_section_id) {
				$section->parent->questions()->where('order', '>=', $section->order)->get()->each(function ($other) {
					$other->order += 1;
					$other->save();
				});

				return $this->returnSuccessMessage('data', new SectionResource($section->parent));
			}

			return $this->returnSuccessMessage('data', null);
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
