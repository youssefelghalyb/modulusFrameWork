<?php

namespace Modules\UserManagement\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\UserManagement\Http\Requests\LoginRequest;
use Modules\UserManagement\Http\Requests\RegisterRequest;
use Modules\UserManagement\Services\AuthenticationService;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());

            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                'data' => [
                    'user_id' => $user->user_id,
                    'email' => $user->email_address
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $deviceInfo = [
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'device_type' => $request->header('X-Device-Type', 'unknown')
        ];

        $result = $this->authService->login(
            $request->email,
            $request->password,
            $deviceInfo
        );

        if (!$result) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'user' => [
                    'id' => $result['user']->user_id,
                    'email' => $result['user']->email_address,
                    'profile' => $result['user']->profile
                ],
                'device' => [
                    'id' => $result['device']->device_id,
                    'type' => $result['device']->device_type,
                    'trusted' => $result['device']->is_trusted
                ]
            ]
        ]);
    }

    public function verifyEmail(string $token): JsonResponse
    {
        if ($this->authService->verifyEmail($token)) {
            return response()->json([
                'message' => 'Email verified successfully'
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired verification token'
        ], Response::HTTP_BAD_REQUEST);
    }

    public function logout(Request $request): JsonResponse
    {
        // Implement token revocation here
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        // Implement refresh token logic here
        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => 'new_access_token'
            ]
        ]);
    }
}