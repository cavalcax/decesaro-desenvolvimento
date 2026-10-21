<?php

declare(strict_types=1);

namespace Config;

use App\Helpers\Env;

class MailConfig
{
    public static function get(): array
    {
        return [
            'driver' => (string)Env::get('MAIL_DRIVER', 'smtp'),
            'host' => (string)Env::get('MAIL_HOST', 'sandbox.smtp.mailtrap.io'),
            'port' => (int)Env::get('MAIL_PORT', 2525),
            'username' => (string)Env::get('MAIL_USERNAME', ''),
            'password' => (string)Env::get('MAIL_PASSWORD', ''),
            'encryption' => (string)Env::get('MAIL_ENCRYPTION', 'tls'),
            'from_address' => (string)Env::get('MAIL_FROM_ADDRESS', 'desenvolvimento@decesaro.com'),
            'from_name' => (string)Env::get('MAIL_FROM_NAME', 'Desenvolvimento - Família De Cesaro'),
        ];
    }
}
