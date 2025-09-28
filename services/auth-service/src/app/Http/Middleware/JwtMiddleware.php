<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
    try {
      $user = JWTAuth::parseToken()->authenticate();

      if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
      }

      if (!$user->is_active) {
        return response()->json(['message' => 'Account is deactivated'], 403);
      }

      // Устанавливаем пользователя в Auth guard
      auth()->setUser($user);
      $request->setUserResolver(function () use ($user) {
        return $user;
      });

    } catch (TokenExpiredException $e) {
      return response()->json(['message' => 'Token expired'], 401);
    } catch (TokenInvalidException $e) {
      return response()->json(['message' => 'Token invalid'], 401);
    } catch (JWTException $e) {
      return response()->json(['message' => 'Token absent'], 401);
    }

    return $next($request);
  }
}
