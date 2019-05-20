<?php

namespace App\Http\Middleware;

use App\Services\AclService;
use Auth;
use Closure;

class ApplicationAdminMiddleware
{
    private $aclService;

    public function __construct(AclService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Ensure user is an adminstrator of the given application. 
     * *Note* This is dependent on the application being available on the requests attribute 
     * param bag. This can be done by layering this middleware _after_ the 
     * ResolveApplicationSlugMiddleware
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $application = $request->get('application');
        $this->aclService->adminOrfail($user, $application);

        return $next($request);
    }
}