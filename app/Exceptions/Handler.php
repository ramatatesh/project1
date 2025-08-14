<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => __('app.unauthenticated')
        ], 401);
    }

    public function register(): void
    {
        //
    }
}
