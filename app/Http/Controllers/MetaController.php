<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Meta;
use App\Http\Resources\MetaResource;
use Illuminate\Http\Request;

class MetaController extends Controller
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
		$metas = Meta::all();
		return $this->returnSuccessMessage('metas', MetaResource::collection($metas));
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
            'metable_id' => 'integer'
		]);

		try {
			// Create meta
			$meta = Meta::create($request->only('metadata', 'metable_id', 'metable_type'));

			if ($meta) {
				return $this->returnSuccessMessage('meta', new MetaResource($meta));
			}

			// Send error if meta is not created
			return $this->returnError('meta', 503, 'create');
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
        $meta = Meta::find($id);
		if ($meta) {
			return $this->returnSuccessMessage('meta', new MetaResource($meta));
		}

		// Send error if meta does not exist
		return $this->returnError('meta', 404, 'show');
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
            'metable_id' => 'integer'
        ]);

		try {
			$meta = Meta::find($id);

			// Send error if meta does not exist
			if (!$meta) {
				return $this->returnError('meta', 404, 'update');
			}

			// Update meta
			if ($meta->fill($request->only('metadata', 'metable_id', 'metable_type'))->save()) {
				return $this->returnSuccessMessage('meta', new MetaResource($meta));
			}

			// Send error if there is an error on update
			return $this->returnError('meta', 503, 'update');
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
			$meta = Meta::find($id);

			// Send error if meta does not exist
			if (!$meta) {
				return $this->returnError('meta', 404, 'delete');
			}

			// Delete meta
			if ($meta->delete()) {
				return $this->returnSuccessMessage('message', 'Meta has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('meta', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
