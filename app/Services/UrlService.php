<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Str;

class UrlService {

    public function getApplication(Application $application, $path = '') 
    {
        $scheme = config('services.app.frontend_scheme');
        $host = config('services.app.frontend_base_domain');
        \Log::info("INVITE-URL-ISSUE--UrlService@getApplication:: Path: $path. Scheme: [$scheme]. Host: [$host]. Application: " . json_encode($application));
        return "$scheme://{$application->slug}.{$host}{$path}";
    }

    public function constructEncodedData($key, $parameters = null) 
    {
        if(is_null($parameters)) {
            return '';
        }
        return "?$key=" . urlencode(base64_encode(json_encode($parameters)));
    }

    public function getApplicationLogin($application, $parameters = null) 
    {
        $query = $this->constructEncodedData('invite', $parameters);
        \Log::info("INVITE-URL-ISSUE--UrlService@getApplicationLogin:: Query: $query. Application: " . json_encode($application));
        return $this->getApplication($application, "/login{$query}");
    }

    public function getApplicationRegister($application, $parameters = null) 
    {
        $query = $this->constructEncodedData('invite', $parameters);
        \Log::info("INVITE-URL-ISSUE--UrlService@getApplicationRegister:: Query: $query. Application: " . json_encode($application));
        return $this->getApplication($application, "/register{$query}");
    }
}