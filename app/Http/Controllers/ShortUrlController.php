<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\ShortUrl;
use App\Http\Resources\ShortUrlResource;
use Illuminate\Http\Request;

class ShortUrlController extends Controller
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
		$short_urls = ShortUrl::all();
		return $this->returnSuccessMessage('short_urls', ShortUrlResource::collection($short_urls));
	}

    /**
     * Display the specified resource.
     *
     * @param  string $short_url
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($short_url)
    {
        $short_url = ShortUrl::where('short_url', $short_url)->first();
        if ($short_url) {
            return $this->returnSuccessMessage('short_url', new ShortUrlResource($short_url));
        }

        // Send error if short_url does not exist
        return $this->returnError('short_url', 404, 'show');
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
			'url' => 'required'
		]);

		try {
            $url = $request->input('url', null);

			// Check url
            $short_url = ShortUrl::where([
                ['url', '=', $url]
            ])->first();

            if (empty($short_url)) {
                // Create ShortUrl
                $short = base64_encode(str_random(40));
                while (!is_null(ShortUrl::where('short_url', $short)->first())) {
                    $short = base64_encode(str_random(40));
                }
                $short_url = ShortUrl::create([
                    'url' => $url,
                    'short_url' => $short
                ]);
            }

            if ($short_url) {
                return $this->returnSuccessMessage('short_url', new ShortUrlResource($short_url));
            }

			// Send error if short_url is not created
			return $this->returnError('short_url', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
