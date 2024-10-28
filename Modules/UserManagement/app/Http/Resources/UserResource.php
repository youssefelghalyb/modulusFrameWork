<?php

namespace Modules\UserManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'email' => $this->email_address,
            'status' => [
                'id' => $this->status->user_status_id,
                'name' => $this->status->status_name,
                'is_active' => $this->status->is_active,
            ],
            'email_verification' => [
                'status' => $this->emailStatus->status_description,
                'verified' => $this->emailStatus->status_description === 'Verified',
                'verified_at' => $this->when(
                    $this->email_verified_at,
                    fn() => $this->email_verified_at->toIso8601String()
                ),
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'devices' => DeviceResource::collection($this->whenLoaded('devices')),
        ];
    }
}

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->profile_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'avatar' => $this->when($this->avatar, fn() => [
                'url' => url(Storage::url($this->avatar)),
                'thumbnail' => url(Storage::url('thumbnails/' . $this->avatar))
            ]),
            'meta_data' => [
                'date_of_birth' => $this->meta_data['date_of_birth'] ?? null,
                'address' => $this->meta_data['address'] ?? null,
                'social_links' => $this->meta_data['social_links'] ?? [],
                'bio' => $this->meta_data['bio'] ?? null,
                'preferences' => $this->meta_data['preferences'] ?? [
                    'language' => config('app.locale'),
                    'timezone' => config('app.timezone'),
                    'notifications' => [
                        'email' => true,
                        'push' => true,
                        'sms' => false
                    ]
                ]
            ],
            'last_updated' => $this->last_updated->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String()
        ];
    }
}

class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->device_id,
            'type' => $this->device_type,
            'details' => [
                'browser' => $this->browser,
                'browser_version' => $this->browser_version,
                'operating_system' => $this->operating_system,
                'ip_address' => $this->ip_address,
            ],
            'security' => [
                'is_trusted' => $this->is_trusted,
                'is_current' => $this->device_id === $request->header('X-Device-ID'),
            ],
            'activity' => [
                'last_login' => $this->last_login_at->toIso8601String(),
                'last_login_human' => $this->last_login_at->diffForHumans(),
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String()
        ];
    }
}

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->access_token,
            'expires_in' => config('sanctum.expiration') * 60, // Convert to seconds
            'refresh_token' => $this->when($this->refresh_token, fn() => $this->refresh_token),
            'user' => new UserResource($this->user),
            'device' => new DeviceResource($this->device),
        ];
    }
}