<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\DeviceFingerprint;
use App\Models\User;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        if ($this->authenticationService->isLockedOut($username)) {
            return $this->error('Account is temporarily locked due to too many failed attempts.', 423);
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->authenticationService->recordFailedAttempt($username, $request->ip() ?? '0.0.0.0');
            return $this->error('Invalid credentials.', 401);
        }

        // Delete old tokens for this user
        $user->tokens()->delete();

        // Create a new Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        // Record device fingerprint if provided
        if ($request->filled('fingerprint_hash')) {
            DeviceFingerprint::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fingerprint_hash' => $request->input('fingerprint_hash'),
                ],
                [
                    'fingerprint_encrypted' => $request->input('fingerprint_hash'),
                    'ip_address_encrypted' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_seen_at' => now(),
                ],
            );
        }

        $user->load('roles');

        return $this->success([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->error('Unauthenticated.', 401);
        }

        $user->load('roles');

        return $this->success($user);
    }
}
