<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

class AuthService extends BaseService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'customer';

        /** @var User $user */
        $user = $this->userRepository->create($data);

        return $user;
    }

    /**
     * Authenticate user and return JWT token.
     *
     * @throws Throwable
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        $token = JWTAuth::fromUser($user);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
        ];
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(): array
    {
        try {
            // Try to refresh using JWTAuth
            $token = JWTAuth::refresh();
        } catch (\Exception) {
            // If refresh fails, get the user from the token and generate a new one
            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::fromUser($user);
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Get authenticated user profile.
     */
    public function getProfile(User $user): User
    {
        return $user;
    }
}
