<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\Repositories\ApplicationRepository;

class ResolveApplicationSlugMiddleware
{
    protected $applicationRepository;

    public function __construct(ApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * Ensure user is an adminstrator of the given application
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // LARAVEL_MIGRATION: If migrated, $request->route() is an object, not an array
        // Lumen doesn't give a pretty route object to work with, index 2 _always_ contains our
        // route params
        //
        $applicationSlug = data_get($request->route(), '2.application_slug', null);
        $application = $this->applicationRepository->bySlug(Auth::user(), $applicationSlug);
        $request->attributes->add(compact('application'));

        return $next($request);
    }
}