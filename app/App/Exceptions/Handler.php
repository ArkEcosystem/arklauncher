<?php

declare(strict_types=1);

namespace App\Exceptions;

use ARKEcosystem\Foundation\UserInterface\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  Throwable  $exception
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     *
     * @throws Throwable
     * @return \Illuminate\Http\JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
