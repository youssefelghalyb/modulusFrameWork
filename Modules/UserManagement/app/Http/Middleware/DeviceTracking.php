<?php

namespace Modules\UserManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\UserManagement\Models\UserDevice;
use Symfony\Component\HttpFoundation\Response;

class DeviceTracking
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $deviceInfo = [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'device_type' => $request->header('X-Device-Type', 'web')
            ];

            $deviceId = UserDevice::generateDeviceId($deviceInfo);
            $request->headers->set('X-Device-ID', $deviceId);

            // Update or create device record
            $device = UserDevice::firstOrCreate(
                ['device_id' => $deviceId],
                array_merge(
                    [
                        'user_id' => $request->user()->user_id,
                    ],
                    UserDevice::parseUserAgent($deviceInfo['user_agent']),
                    [
                        'ip_address' => $deviceInfo['ip_address'],
                        'last_login_at' => now()
                    ]
                )
            );

            // Update last login time
            if (!$device->wasRecentlyCreated) {
                $device->updateLastLogin();
            }

            // Add device to request for later use
            $request->attributes->set('device', $device);
        }

        return $next($request);
    }
}