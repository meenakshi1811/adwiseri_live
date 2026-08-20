<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ErrorLogService
{
    /**
     * Persist an exception for admin review.
     */
    public function log(Throwable $exception, ?Request $request = null): ?ErrorLog
    {
        if ($exception instanceof ValidationException) {
            return null;
        }

        $request = $request ?: request();

        $requestPath = '/' . ltrim($request->path(), '/');
        if ($exception instanceof NotFoundHttpException
            && $this->isAssetRequest($requestPath)
            && !$request->headers->has('referer')) {
            return null;
        }

        try {
            return ErrorLog::create([
                'error_type' => $this->resolveErrorType($exception),
                'page_screen' => $this->resolvePageScreen($request),
                'message' => $this->resolveMessage($exception),
                'status_code' => $this->resolveStatusCode($exception),
                'user_id' => optional($request->user())->id,
                'ip_address' => $request->ip(),
                'stack_trace' => $this->resolveStackTrace($exception),
            ]);
        } catch (Throwable $loggingFailure) {
            return null;
        }
    }

    private function resolveErrorType(Throwable $exception): string
    {
        $class = class_basename($exception);

        if ($exception instanceof NotFoundHttpException) {
            return 'NotFoundHttpException';
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $class . ' (' . $exception->getStatusCode() . ')';
        }

        return $class;
    }

    private function resolvePageScreen(?Request $request): ?string
    {
        if (!$request) {
            return null;
        }

        $requestPath = '/' . ltrim($request->path(), '/');
        $pagePath = $requestPath;
        $pageUrl = $request->fullUrl();

        if ($this->isAssetRequest($requestPath)) {
            $referer = trim((string) $request->headers->get('referer'));
            $refererPath = $referer !== '' ? (parse_url($referer, PHP_URL_PATH) ?: null) : null;

            if ($refererPath && !$this->isAssetRequest($refererPath)) {
                $pagePath = '/' . ltrim($refererPath, '/');
                $pageUrl = $referer;
            }
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        if ($routeName && $pagePath === $requestPath) {
            return $routeName . ' (' . $pagePath . ')';
        }

        if ($pagePath !== $requestPath) {
            return $pageUrl . ' (asset: ' . $requestPath . ')';
        }

        return $pageUrl;
    }

    private function isAssetRequest(string $path): bool
    {
        $path = strtolower('/' . ltrim($path, '/'));

        $extensions = [
            '.css', '.js', '.map', '.json', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.ico',
            '.woff', '.woff2', '.ttf', '.eot', '.otf', '.mp4', '.webm', '.pdf',
        ];

        foreach ($extensions as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }

        $prefixes = ['/css/', '/js/', '/images/', '/img/', '/fonts/', '/web_assets/', '/admin_assets/', '/vendor/', '/build/'];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolveMessage(Throwable $exception): string
    {
        $message = trim((string) $exception->getMessage());

        if ($message !== '') {
            return mb_substr($message, 0, 65000);
        }

        return class_basename($exception);
    }

    private function resolveStatusCode(Throwable $exception): ?int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function resolveStackTrace(Throwable $exception): ?string
    {
        $trace = $exception->getTraceAsString();

        return $trace !== '' ? mb_substr($trace, 0, 65000) : null;
    }
}
