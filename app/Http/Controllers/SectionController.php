<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Application;
use App\Models\FormTemplate;
use App\Models\Section;
use App\Http\Resources\SectionResource;
use App\Http\Resources\FormTemplateSectionResource;
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
	 * @param  int $form_template_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_template_id)
	{
		$form_template = FormTemplate::with(['sections.questions.answers', 'sections.questions.metas'])->find($form_template_id);
		return $this->returnSuccessMessage('sections', SectionResource::collection($form_template->sections));
	}

    /**
     * Display a listing of the resource.
     *
     * @param  string $application_slug
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all($application_slug)
    {
        $user = Auth::user();

        if($user->role->name === 'Super Admin') {
            $application = Application::with(['form_templates.sections.questions.answers', 'form_templates.sections.questions.questionType', 'form_templates.sections.questions.metas', 'form_templates.metas'])->where('slug', $application_slug)->first();
        } else {
            //$user->load('applications.form_templates.metas');
            $application = $user->applications()->with(['form_templates.sections.questions.answers', 'form_templates.sections.questions.questionType', 'form_templates.sections.questions.metas', 'form_templates.metas'])->where('slug', $application_slug)->first();
        }

        // No Application
        if (!$application) {
            return $this->returnApplicationNameError();
        }

        $form_templates = $application->form_templates;
//        return $this->returnSuccessMessage('sections', array_map(function ($form_template) {
//            return SectionResource::collection($form_template->sections);
//        }, $form_templates));
        return $this->returnSuccessMessage('form_templates', FormTemplateSectionResource::collection($application->form_templates));
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_template_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_template_id, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'parent_section_id' => 'nullable|integer|min:1'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'create section');
			}

			// Count order
			$order = 1;
			$parent_section_id = $request->input('parent_section_id', null);
			if (!$parent_section_id) {
				if (count($form_template->sections) > 0) {
					$order = $form_template->sections()->where('parent_section_id', null)->max('order') + 1;
				}
			} else {
				$parent_section = $form_template->sections()->find($parent_section_id);

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
			$section = $form_template->sections()->create([
				'name' => $request->input('name'),
				'parent_section_id' => $parent_section_id,
				'order' => $order,
				'min_rows' => 1
			]);

			if ($section) {
				return $this->returnSuccessMessage('section', new SectionResource(Section::find($section->id)));
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
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function duplicate($form_template_id, $id)
	{
		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'duplicate section');
			}

			$section = $form_template->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'duplicate');
			}

			// Duplicate section
			$newSection = $form_template->sections()->create([
				'name' => $section->name,
				'parent_section_id' => $section->parent_section_id,
				'order' => ($section->order + 1),
				'repeatable' => $section->repeatable,
				'max_rows' => $section->max_rows,
				'min_rows' => $section->min_rows
			]);

			if ($newSection) {
				// Update other sections order
				$form_template->sections()->where([
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

				return $this->returnSuccessMessage('section', new SectionResource(Section::find($newSection->id)));
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
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($form_template_id, $id)
	{
		$form_template = FormTemplate::find($form_template_id);

		// Send error if form_template does not exist
		if (!$form_template) {
			return $this->returnError('form_template', 404, 'show section');
		}

		$section = $form_template->sections()->find($id);
		if ($section) {
			return $this->returnSuccessMessage('section', new SectionResource($section));
		}

		// Send error if section does not exist
		return $this->returnError('section', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_template_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($form_template_id, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191',
            'repeatable' => 'filled|integer',
            'max_rows' => 'filled|integer',
            'min_rows' => 'filled|integer'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'update section');
			}

			$section = $form_template->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update');
			}

			// Update section
			if ($section->fill($request->only('name', 'repeatable', 'max_rows', 'min_rows'))->save()) {
				return $this->returnSuccessMessage('section', new SectionResource(Section::find($section->id)));
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
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($form_template_id, $id)
	{
		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'delete section');
			}

			$section = $form_template->sections()->find($id);

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
	 * @param $form_template_id
	 * @param $id
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function move($form_template_id, $id, Request $request)
	{
		$this->validate($request, [
			'parent_section_id' => 'nullable|integer|min:1',
			'order' => 'required|integer|min:1'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'move section');
			}

			$section = $form_template->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'move');
			}

			$parent_section_id = $request->input('parent_section_id', null);
			if ($parent_section_id) {
				$parent_section = $form_template->sections()->find($parent_section_id);

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
			$form_template->sections()->where([
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
