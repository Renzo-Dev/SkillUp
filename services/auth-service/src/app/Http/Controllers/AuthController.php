<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\LogoutResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\RegisterResource;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register($request->validated());
        if ($data) {
            return new RegisterResource($data);
        } else {
            return response()->json(new RegisterResource(false), 500);
        }
    }

    public function logout(Request $request)
    {
        if ($this->authService->logout()) {
            return new LogoutResource(true);
        } else {
            return new LogoutResource(false);
        }
    }
}
