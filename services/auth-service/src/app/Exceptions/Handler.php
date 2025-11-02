<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, \Throwable $exception): Response
    {
        // Обработка JWT ошибок
        if ($exception instanceof TokenExpiredException) {
            return $this->handleJWTError('Токен истек', 'TOKEN_EXPIRED', 401);
        }

        if ($exception instanceof TokenInvalidException) {
            return $this->handleJWTError('Токен недействителен', 'TOKEN_INVALID', 401);
        }

        if ($exception instanceof TokenBlacklistedException) {
            return $this->handleJWTError('Токен отозван', 'TOKEN_BLACKLISTED', 401);
        }

        if ($exception instanceof JWTException) {
            // Проверяем конкретные сообщения об ошибках
            $message = $exception->getMessage();
            
            if (str_contains($message, 'Token not provided')) {
                return $this->handleJWTError('Токен не предоставлен', 'TOKEN_MISSING', 401);
            }
            
            if (str_contains($message, 'The token has been blacklisted')) {
                return $this->handleJWTError('Токен отозван', 'TOKEN_BLACKLISTED', 401);
            }
            
            if (str_contains($message, 'Token could not be parsed')) {
                return $this->handleJWTError('Токен недействителен', 'TOKEN_INVALID', 401);
            }
            
            return $this->handleJWTError('Ошибка аутентификации', 'JWT_ERROR', 401);
        }

        return parent::render($request, $exception);
    }

    /**
     * Обработка JWT ошибок с кастомным форматом ответа
     */
    private function handleJWTError(string $message, string $errorCode, int $statusCode): JsonResponse
    {
        Log::warning("JWT ошибка: {$message}", [
            'error_code' => $errorCode,
            'status_code' => $statusCode
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode
        ], $statusCode);
    }
}
