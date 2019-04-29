<?php

use \Illuminate\Support\Str;

if (!function_exists('config_path')) {
	/**
	 * Get the configuration path.
	 *
	 * @param string $path
	 * @return string
	 */
	function config_path($path = '')
	{
		return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
	}
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder. (this is from core laravel)
     *
     * @param  string  $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('model_class')) {
    /**
     * Return the fully qualified model class name from the resource name. For example, 
     * form_templates would become App\Models\FormTemplate. Which then can be used
     * directly:
     * $model = model_class('form_templates');
     * $formTemplate = $model::find(1);
     *
     * @param string $resource
     * @return string
     */
    function model_class($resource)
    {
        return "App\\Models\\" . Str::singular(Str::studly($resource));
    }
}