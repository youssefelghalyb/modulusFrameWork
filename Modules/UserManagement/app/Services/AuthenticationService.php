<?php

namespace Modules\UserManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\UserManagement\Models\EmailValidation;
use Modules\UserManagement\Models\User;
use Modules\UserManagement\Models\UserDevice;
use Modules\UserManagement\Models\UserStatusType;

class AuthenticationService
{
    public function __construct(
        private readonly HashingService $hashingService
    ) {}

    public function register(array $userData): User
    {
        try {
            DB::beginTransaction();

            // Hash the password
            $hashedPassword = $this->hashingService->hash($userData['password']);

            // Create user login data
            $user = User::create([
                'login_name' => $userData['email'],
                'password_hash' => $hashedPassword['hash'],
                'password_salt' => $hashedPassword['salt'],
                'hash_algorithm_id' => $hashedPassword['algorithm_id'],
                'email_address' => $userData['email'],
                'confirmation_token' => Str::random(64),
                'token_generation_time' => now(),
                'email_validation_status_id' => EmailValidation::where('status_description', 'Pending Verification')->first()->email_validation_status_id,
                'user_status_id' => UserStatusType::where('status_name', 'Inactive')->first()->user_status_id
            ]);

            // Create user profile
            User::create([
                'user_id' => $user->user_id,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone_number' => $userData['phone_number'] ?? null,
                'meta_data' => [
                    'registration_ip' => request()->ip(),
                    'registration_date' => now()->toDateTimeString(),
                ]
            ]);

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function login(string $email, string $password, array $deviceInfo): ?array
    {
        $user = User::where('email_address', $email)
            ->with(['profile', 'status'])
            ->first();

        if (!$user || !$user->authenticate($password)) {
            return null;
        }

        // Track device
        $deviceId = UserDevice::generateDeviceId($deviceInfo);
        $device = UserDevice::firstOrCreate(
            ['device_id' => $deviceId],
            array_merge(
                ['user_id' => $user->user_id],
                UserDevice::parseUserAgent($deviceInfo['user_agent']),
                [
                    'ip_address' => $deviceInfo['ip_address'],
                    'last_login_at' => now()
                ]
            )
        );

        $device->updateLastLogin();

        // Generate tokens
        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user, $device);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'device' => $device
        ];
    }

    public function verifyEmail(string $token): bool
    {
        $user = User::where('confirmation_token', $token)
            ->where('token_generation_time', '>', now()->subHours(24))
            ->first();

        if (!$user) {
            return false;
        }

        $user->email_validation_status_id = EmailValidation::where('status_description', 'Verified')
            ->first()
            ->email_validation_status_id;
        $user->confirmation_token = null;
        $user->token_generation_time = null;
        $user->user_status_id = UserStatusType::where('status_name', 'Active')
            ->first()
            ->user_status_id;

        return $user->save();
    }

    private function generateAccessToken(User $user): string
    {
        // Implement JWT token generation here
        return Str::random(64);
    }

    private function generateRefreshToken(User $user, UserDevice $device): string
    {
        // Implement refresh token generation and storage here
        return Str::random(64);
    }
}