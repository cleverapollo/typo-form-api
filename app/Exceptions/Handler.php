<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 403);
        }
        
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            $message = "There is no {$model} with this ID.";

            return response()->json([
                'status' => 'fail',
                'message' => $message,
            ], 404);
        }

        if($e instanceof QueryException) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to update resource. Please try again later.',
            ], 503);
        }

        if($e instanceof ApiException) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return parent::render($request, $e);
    }

    public function renderForConsole($output, Exception $e)
    {
        $output->writeln($e->getMessage());
    }
}
