<?php

namespace App\Exceptions;

use App\Services\ErrorLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            app(ErrorLogService::class)->log($e, request());
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();

        if ($e instanceof TokenMismatchException) {
            Auth::logout();

            if ($wantsJson) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'error' => [
                        'code' => SymfonyResponse::HTTP_PAGE_EXPIRED,
                        'message' => 'Your session has expired. Please refresh the page and try again.',
                    ],
                ], SymfonyResponse::HTTP_PAGE_EXPIRED);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'login_error' => 'Your session has expired or changed due to another login. Please log in again.',
                ]);
        }

        if ($e instanceof ValidationException && $wantsJson) {
            $errors = $e->errors();
            $firstMessage = collect($errors)->flatten()->first();

            return response()->json([
                'message' => $firstMessage ?: 'The given data was invalid.',
                'errors' => $errors,
            ], $e->status);
        }

        if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
            return parent::render($request, $e);
        }

        $statusCode = $this->resolveStatusCode($e);
        $message = $this->resolveErrorMessage($e, $statusCode, $request);

        if ($wantsJson) {
            return response()->json([
                'message' => $message,
                'error' => [
                    'code' => $statusCode,
                    'message' => $message,
                ],
            ], $statusCode);
        }

        if (view()->exists('errors.generic')) {
            return response()->view('errors.generic', [
                'statusCode' => $statusCode,
                'message' => $message,
            ], $statusCode);
        }

        return parent::render($request, $e);
    }

    /**
     * Resolve the appropriate HTTP status code for an exception.
     */
    private function resolveStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        if ($e instanceof NotFoundHttpException) {
            return SymfonyResponse::HTTP_NOT_FOUND;
        }

        if ($e instanceof UnauthorizedHttpException || $e instanceof AuthenticationException) {
            return SymfonyResponse::HTTP_UNAUTHORIZED;
        }

        if ($e instanceof AuthorizationException) {
            return SymfonyResponse::HTTP_FORBIDDEN;
        }

        if ($e instanceof TokenMismatchException) {
            return SymfonyResponse::HTTP_PAGE_EXPIRED;
        }

        if ($e instanceof ValidationException) {
            return SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY;
        }

        if ($e instanceof ThrottleRequestsException) {
            return SymfonyResponse::HTTP_TOO_MANY_REQUESTS;
        }

        return SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Resolve a safe, user-facing error message.
     */
    private function resolveErrorMessage(Throwable $e, int $statusCode, $request): string
    {
        if ($this->isClientFacingRequest($request)) {
            return $this->maskedClientMessage($statusCode);
        }

        if ($e instanceof HttpExceptionInterface && !empty($e->getMessage())) {
            return $e->getMessage();
        }

        if ($e instanceof ValidationException) {
            return $e->getMessage() ?: 'Validation failed.';
        }

        return SymfonyResponse::$statusTexts[$statusCode] ?? 'An unexpected error occurred.';
    }

    /**
     * Mask technical errors on subscriber/client/public screens.
     */
    private function isClientFacingRequest($request): bool
    {
        $user = $request->user();

        if ($user && strtolower((string) $user->user_type) === 'admin') {
            return false;
        }

        return true;
    }

    /**
     * Generic messages shown to non-admin users.
     */
    private function maskedClientMessage(int $statusCode): string
    {
        $messages = [
            SymfonyResponse::HTTP_BAD_REQUEST => 'The request could not be processed. Please check your input and try again.',
            SymfonyResponse::HTTP_UNAUTHORIZED => 'You are not authorized to access this page.',
            SymfonyResponse::HTTP_FORBIDDEN => 'You do not have permission to perform this action.',
            SymfonyResponse::HTTP_NOT_FOUND => 'The page you are looking for could not be found.',
            SymfonyResponse::HTTP_METHOD_NOT_ALLOWED => 'This action is not allowed.',
            SymfonyResponse::HTTP_PAGE_EXPIRED => 'Your session has expired. Please refresh the page and try again.',
            SymfonyResponse::HTTP_TOO_MANY_REQUESTS => 'Too many requests. Please wait a moment and try again.',
            SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR => 'Something went wrong. Please try again later.',
            SymfonyResponse::HTTP_SERVICE_UNAVAILABLE => 'The service is temporarily unavailable. Please try again later.',
        ];

        return $messages[$statusCode] ?? 'Something went wrong. Please try again later.';
    }
}
