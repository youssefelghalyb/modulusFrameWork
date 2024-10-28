<?php

namespace Modules\UserManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\UserManagement\Http\Requests\ProfileUpdateRequest;
use Modules\UserManagement\Http\Requests\PasswordUpdateRequest;
use Modules\UserManagement\Services\HashingService;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly HashingService $hashingService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->with('devices')->first();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->user_id,
                    'email' => $user->email_address,
                    'status' => $user->status->status_name,
                    'email_verified' => $user->emailStatus->status_description === 'Verified'
                ],
                'profile' => [
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'full_name' => $profile->full_name,
                    'phone_number' => $profile->phone_number,
                    'avatar' => $profile->avatar ? url(Storage::url($profile->avatar)) : null,
                    'meta_data' => $profile->meta_data,
                    'last_updated' => $profile->last_updated->toIso8601String()
                ],
                'devices' => $profile->devices->map(fn($device) => [
                    'device_id' => $device->device_id,
                    'device_type' => $device->device_type,
                    'browser' => $device->browser,
                    'operating_system' => $device->operating_system,
                    'last_login' => $device->last_login_at->diffForHumans(),
                    'is_trusted' => $device->is_trusted
                ])
            ]
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile;
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        if ($profile->updateProfile($data)) {
            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'profile' => $profile->fresh()
                ]
            ]);
        }

        return response()->json([
            'message' => 'Failed to update profile'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $hashedPassword = $this->hashingService->hash($data['password']);

        $user->password_hash = $hashedPassword['hash'];
        $user->password_salt = $hashedPassword['salt'];
        $user->hash_algorithm_id = $hashedPassword['algorithm_id'];

        if ($user->save()) {
            // Log the password change
            $user->profile->setMetaData('last_password_change', now()->toIso8601String());

            return response()->json([
                'message' => 'Password updated successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to update password'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function devices(Request $request): JsonResponse
    {
        $devices = $request->user()
            ->devices()
            ->orderBy('last_login_at', 'desc')
            ->get();

        return response()->json([
            'data' => $devices->map(fn($device) => [
                'device_id' => $device->device_id,
                'device_type' => $device->device_type,
                'browser' => $device->browser,
                'browser_version' => $device->browser_version,
                'operating_system' => $device->operating_system,
                'ip_address' => $device->ip_address,
                'last_login' => $device->last_login_at->diffForHumans(),
                'is_trusted' => $device->is_trusted,
                'is_current' => $device->device_id === $request->header('X-Device-ID')
            ])
        ]);
    }

    public function revokeDevice(Request $request, string $deviceId): JsonResponse
    {
        $device = $request->user()
            ->devices()
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($device->device_id === $request->header('X-Device-ID')) {
            return response()->json([
                'message' => 'Cannot revoke current device'
            ], Response::HTTP_BAD_REQUEST);
        }

        $device->delete();

        return response()->json([
            'message' => 'Device access revoked successfully'
        ]);
    }

    public function trustDevice(Request $request, string $deviceId): JsonResponse
    {
        $device = $request->user()
            ->devices()
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $device->markAsTrusted();

        return response()->json([
            'message' => 'Device marked as trusted',
            'data' => [
                'device' => $device
            ]
        ]);
    }
}