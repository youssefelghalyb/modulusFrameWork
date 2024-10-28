<?php

namespace Modules\UserManagement\Http\Controllers\Web\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Http\Requests\ProfileUpdateRequest;
use Modules\UserManagement\Http\Requests\PasswordUpdateRequest;
use Modules\UserManagement\Models\UserDevice;
use Modules\UserManagement\Services\HashingService;

class ProfileController extends Controller
{
    public function __construct(
        private readonly HashingService $hashingService
    ) {}

    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;
        $devices = $user->devices()
            ->orderBy('last_login_at', 'desc')
            ->get();

        return view('usermanagement::profile.show', compact('user', 'profile', 'devices'));
    }

    public function edit(Request $request)
    {
        $profile = $request->user()->profile;
        return view('usermanagement::profile.edit', compact('profile'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $profile = $user->profile;
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        if ($profile->updateProfile($data)) {
            return redirect()
                ->route('profile.show')
                ->with('success', 'Profile updated successfully.');
        }

        return back()
            ->withInput()
            ->with('error', 'Failed to update profile.');
    }

    public function editPassword()
    {
        return view('usermanagement::profile.password');
    }

    public function updatePassword(PasswordUpdateRequest $request)
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

            return redirect()
                ->route('profile.show')
                ->with('success', 'Password updated successfully.');
        }

        return back()->with('error', 'Failed to update password.');
    }

    public function devices(Request $request)
    {
        $devices = $request->user()
            ->devices()
            ->orderBy('last_login_at', 'desc')
            ->paginate(10);

        return view('usermanagement::profile.devices', compact('devices'));
    }

    public function revokeDevice(Request $request, string $deviceId)
    {
        $device = $request->user()
            ->devices()
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {
            return back()->with('error', 'Device not found.');
        }

        if ($device->device_id === UserDevice::generateDeviceId([
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'device_type' => 'web'
        ])) {
            return back()->with('error', 'Cannot revoke current device.');
        }

        $device->delete();

        return back()->with('success', 'Device access revoked successfully.');
    }

    public function trustDevice(Request $request, string $deviceId)
    {
        $device = $request->user()
            ->devices()
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {
            return back()->with('error', 'Device not found.');
        }

        $device->markAsTrusted();

        return back()->with('success', 'Device marked as trusted.');
    }
}