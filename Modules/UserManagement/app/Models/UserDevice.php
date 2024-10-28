<?php

namespace Modules\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'browser',
        'browser_version',
        'operating_system',
        'ip_address',
        'last_login_at',
        'is_trusted'
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_trusted' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function generateDeviceId(array $deviceInfo): string
    {
        return hash('sha256', json_encode([
            $deviceInfo['user_agent'],
            $deviceInfo['ip_address'],
            $deviceInfo['device_type'],
            config('app.key')
        ]));
    }

    public function markAsTrusted(): bool
    {
        $this->is_trusted = true;
        return $this->save();
    }

    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    public static function parseUserAgent(string $userAgent): array
    {
        return [
            'browser' => self::getBrowser($userAgent),
            'browser_version' => self::getBrowserVersion($userAgent),
            'device_type' => self::getDeviceType($userAgent),
            'operating_system' => self::getOperatingSystem($userAgent)
        ];
    }

    private static function getBrowser(string $userAgent): string
    {
        $browsers = [
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Opera' => 'Opera',
            'MSIE' => 'Internet Explorer',
            'Edge' => 'Edge',
            'Safari' => 'Safari'
        ];

        foreach ($browsers as $key => $value) {
            if (strpos($userAgent, $key) !== false) {
                return $value;
            }
        }

        return 'Unknown';
    }

    private static function getBrowserVersion(string $userAgent): string
    {
        $known = ['Version', 'Firefox', 'Chrome', 'Edge', 'MSIE', 'Opera'];
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        
        if (!preg_match_all($pattern, $userAgent, $matches)) {
            return "0";
        }

        $i = count($matches['browser']) - 1;
        return $matches['version'][$i] ?? "0";
    }

    private static function getDeviceType(string $userAgent): string
    {
        $deviceTypes = [
            'Mobile' => 'mobile',
            'Tablet' => 'tablet',
            'iPad' => 'tablet',
            'Android' => 'mobile',
            'iPhone' => 'mobile'
        ];

        foreach ($deviceTypes as $key => $value) {
            if (strpos($userAgent, $key) !== false) {
                return $value;
            }
        }

        return 'desktop';
    }

    private static function getOperatingSystem(string $userAgent): string
    {
        $os = [
            'Windows' => 'Windows',
            'Mac OS X' => 'MacOS',
            'Linux' => 'Linux',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS'
        ];

        foreach ($os as $key => $value) {
            if (strpos($userAgent, $key) !== false) {
                return $value;
            }
        }

        return 'Unknown';
    }
}