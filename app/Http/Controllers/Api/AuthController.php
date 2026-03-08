<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /** POST /api/auth/register */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register($request->validated());

        return response()->json([
            'message'      => 'User registered successfully',
            'user'         => $data['user'],
            'access_token' => $data['token'],
            'token_type'   => 'Bearer',
        ], 201);
    }

    /** POST /api/auth/login */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return response()->json([
            'message'      => 'Login successful',
            'user'         => $data['user'],
            'access_token' => $data['token'],
            'token_type'   => 'Bearer',
        ]);
    }

    /** POST /api/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout successful']);
    }

    /** GET /api/auth/user */
    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    /** PUT /api/auth/update-profile */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }

    /** POST /api/auth/change-password */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return response()->json(['message' => 'Password changed successfully']);
    }
}
