<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\AuthService;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  public function __construct(
    private AuthService $authService,
    private JwtService $jwtService
  ) {
  }

  public function register(RegisterRequest $request): JsonResponse
  {
    $user = $this->authService->register($request->validated());

    return response()->json([
      'message' => 'User registered successfully',
      'user' => $user,
      'access_token' => $this->jwtService->generateAccessToken($user),
      'refresh_token' => $this->jwtService->generateRefreshToken($user)
    ], 201);
  }

  public function login(LoginRequest $request): JsonResponse
  {
    $user = $this->authService->login($request->validated());

    return response()->json([
      'message' => 'Login successful',
      'user' => $user,
      'access_token' => $this->jwtService->generateAccessToken($user),
      'refresh_token' => $this->jwtService->generateRefreshToken($user)
    ]);
  }

  public function refresh(RefreshTokenRequest $request): JsonResponse
  {
    $user = $this->authService->refreshToken($request->validated()['refresh_token']);

    return response()->json([
      'access_token' => $this->jwtService->generateAccessToken($user),
      'refresh_token' => $this->jwtService->generateRefreshToken($user)
    ]);
  }

  public function logout(Request $request): JsonResponse
  {
    $this->authService->logout($request->user());

    return response()->json(['message' => 'Logout successful']);
  }

  public function me(Request $request): JsonResponse
  {
    return response()->json(['user' => $request->user()]);
  }

  public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
  {
    $this->authService->forgotPassword($request->validated()['email']);

    return response()->json(['message' => 'Password reset link sent']);
  }

  public function resetPassword(ResetPasswordRequest $request): JsonResponse
  {
    $this->authService->resetPassword($request->validated());

    return response()->json(['message' => 'Password reset successful']);
  }

  public function verifyEmail(string $token): JsonResponse
  {
    $this->authService->verifyEmail($token);

    return response()->json(['message' => 'Email verified successfully']);
  }

  public function updateProfile(UpdateProfileRequest $request): JsonResponse
  {
    $user = $this->authService->updateProfile($request->user(), $request->validated());

    return response()->json([
      'message' => 'Profile updated successfully',
      'user' => $user
    ]);
  }

  public function updatePassword(UpdatePasswordRequest $request): JsonResponse
  {
    $this->authService->updatePassword($request->user(), $request->validated());

    return response()->json(['message' => 'Password updated successfully']);
  }
}
