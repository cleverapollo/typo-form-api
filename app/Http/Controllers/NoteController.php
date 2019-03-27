<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Note;
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
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$notes = Note::all();
		return $this->returnSuccessMessage('notes', NoteResource::collection($notes));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		try {
			if (!$this->hasPermission()) {
				return $this->returnError('note', 403, 'create');
			}

			// Create Note
            $note = Note::create($request->only('name'));

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
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
        $note = Note::find($id);
		if ($note) {
			return $this->returnSuccessMessage('note', new NoteResource($note));
		}

		// Send error if note does not exist
		return $this->returnError('note', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, Request $request)
	{
		try {
			if (!$this->hasPermission()) {
				return $this->returnError('note', 403, 'update');
			}

            $note = Note::find($id);

			// Send error if note does not exist
			if (!$note) {
				return $this->returnError('note', 404, 'update');
			}

			// Update note
			if ($note->fill($request->only('name'))->save()) {
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
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		try {
            $note = Note::find($id);

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
}
