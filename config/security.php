<?php

declare(strict_types=1);

namespace Config;

use App\Helpers\Env;

class SecurityConfig
{
    public static function sessionLifetime(): int
    {
        return (int)Env::get('SESSION_LIFETIME', 7200);
    }

    public static function sessionName(): string
    {
        return (string)Env::get('SESSION_NAME', 'DC_SESSID');
    }

    public static function maxLoginAttempts(): int
    {
        return (int)Env::get('MAX_LOGIN_ATTEMPTS', 5);
    }

    public static function lockoutMinutes(): int
    {
        return (int)Env::get('LOCKOUT_MINUTES', 15);
    }

    public static function videoCompletionThreshold(): float
    {
        return (float)Env::get('VIDEO_COMPLETION_THRESHOLD', 90.0);
    }

    public static function videoTrackingInterval(): int
    {
        return (int)Env::get('VIDEO_TRACKING_INTERVAL', 5);
    }
}
