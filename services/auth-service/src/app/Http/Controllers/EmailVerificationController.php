<?php

namespace App\Http\Controllers;

use App\Contracts\EmailVerificationServiceInterface;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationServiceInterface $service)
    {
    }

    // Отправка письма подтверждения (для аутентифицированного пользователя)
    public function send(Request $request)
    {
        $user = auth()->user() ?? $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Всегда используем email текущего пользователя
        $verification = $this->service->sendVerificationEmail($user, $user->email);

        return response()->json([
            'message' => 'Verification token generated',
            'expires_at' => $verification->expires_at,
        ]);
    }

    // Подтверждение по токену через JSON (публичный)
    public function verifyJson(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $ok = $this->service->verifyByToken($data['token']);
        if ($ok) {
            return response()->json(['message' => 'Email verified']);
        }
        return response()->json(['message' => 'Invalid or expired token'], 400);
    }

    // Повторная отправка (для аутентифицированного пользователя)
    public function resend(Request $request)
    {
        $user = auth()->user() ?? $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $verification = $this->service->resend($user);
            return response()->json([
                'message' => 'Verification token regenerated',
                'expires_at' => $verification->expires_at,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Email already verified'], 409);
        }
    }
}


