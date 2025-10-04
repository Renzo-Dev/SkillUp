<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request) {
        return response()->json(['message' => 'Hello World']);
    }

    public function register(RegisterRequest $request) {
        return response()->json(['message' => 'Hello World']);
    }
}
