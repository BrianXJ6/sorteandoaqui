<?php

namespace App\Exceptions;

use Illuminate\Http\{
    JsonResponse,
    RedirectResponse,
    Response as HttpResponse,
};

use Inertia\{
    Inertia,
    Response as InertiaResponse,
};

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'current_password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prepare exception for rendering.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $e): InertiaResponse|RedirectResponse|HttpResponse|JsonResponse
    {
        $e = match (true) {
            $e instanceof AuthorizationException => new AuthorizationException(trans($e->getMessage()), $e->getCode(), $e),
            $e instanceof ThrottleRequestsException => new ThrottleRequestsException(trans($e->getMessage()), $e, $e->getHeaders(), $e->getStatusCode()),
            $e instanceof AuthenticationException => new AuthenticationException(trans($e->getMessage()), $e->guards(), $e->redirectTo()),
            $e instanceof NotFoundHttpException => new NotFoundHttpException(trans('Could not find the route.'), $e, $e->getStatusCode(), $e->getHeaders()),
            $e instanceof ModelNotFoundException => new ModelNotFoundException($this->changeTextForModelException($e), $e->getCode(), $e),
            default => $e,
        };

        /** @var \Illuminate\Http\Response */
        $response = parent::render($request, $e);

        if (app()->environment('production') && in_array($response->status(), [500, 503, 404, 403]))
            return Inertia::render('Erros/AppError', ['status' => $response->status()]);

        elseif ($response->status() === 419)
            return back()->with(['message' => 'The page expired, please try again.']);

        return $response;
    }

    /**
     * Prepare message for `ModelNotFoundException`
     *
     * @param \Throwable $e
     *
     * @return string
     */
    protected function changeTextForModelException(Throwable $e): string
    {
        $model = basename($e->getModel() ?? null);
        $ids = $e->getIds() ?? [];

        $message = !empty($model)
            ? trans('no results found for :model', ['model' => __("models.{$model}")])
            : trans('no results found');

        $message .= !empty($ids)
            ? ' ' . trans('with ID: :id.', ['id' => implode(',', $ids)])
            : '.';

        return $message;
    }
}
