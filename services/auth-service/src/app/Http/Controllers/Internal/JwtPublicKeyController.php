<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class JwtPublicKeyController extends Controller
{
    public function __construct(
        private readonly JwtService $jwtService
    ) {}
    
    /**
     * Возвращает публичный RSA ключ для валидации JWT токенов
     * Используется другими сервисами для локальной валидации токенов
     */
    public function __invoke(): Response
    {
        try {
            $publicKey = $this->jwtService->getPublicKey();
            
            return response($publicKey, 200, [
                'Content-Type' => 'application/x-pem-file',
                'Content-Disposition' => 'inline; filename="public.pem"',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'PUBLIC_KEY_NOT_FOUND'
            ], 500);
        }
    }
}

