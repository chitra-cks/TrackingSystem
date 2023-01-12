<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use App\Traits\RestApi;
use Config;

class Handler extends ExceptionHandler
{
    use RestApi;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                [],
                trans('messages.error'),
                trans('messages.data_not_found'),
            );
        }
        $http_status = $this->http_status_codes();
        if (config('app.debug') && config('app.env') == "production") {
            if ($exception->getMessage() == "Unauthenticated.") {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    [],
                    [],
                    $exception->getMessage(),
                    $http_status['HTTP_EXCEPTION']
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_EXCEPTION'),
                    [],
                    [],
                    'Unknown Error, Please try again later.',
                    $http_status['HTTP_EXCEPTION']
                );
            }
        } else {
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_UNAUTHORIZED'),
                    [],
                    [],
                    $exception->getMessage(),
                    $http_status['HTTP_EXCEPTION']
                );
            } else {
                $line = $exception->getLine();
                $filePath = $exception->getFile();
                $filePathInfo = !empty($filePath) ? pathinfo($filePath) : [];
                $filePathInfoName =isset($filePathInfo['basename']) ? $filePathInfo['basename'] : '';

                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_EXCEPTION'),
                    [],
                    [],
                    $exception->getMessage().'. Error occurring on '.$filePathInfoName.' and line number is '.$line.'.',
                    $http_status['HTTP_EXCEPTION']
                );
            }
        }
        return parent::render($request, $exception);
    }
}
