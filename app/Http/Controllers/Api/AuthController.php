<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    /**
     * Authenticate user and return JWT token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            $tokenData = $this->authService->login(
                $credentials['email'],
                $credentials['password'],
                $credentials['session_id'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $tokenData,
            ]);
        } catch (\Exception) {
            return $this->error('Invalid credentials', 401);
        }
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $tokenData = $this->authService->refresh();

        return response()->json([
            'status' => 'success',
            'data' => $tokenData,
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $this->authService->getProfile($request->user());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }
}
