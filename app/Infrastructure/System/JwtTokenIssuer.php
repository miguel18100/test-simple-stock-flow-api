<?php

declare(strict_types=1);

namespace App\Infrastructure\System;

use App\Application\Ports\Outbound\TokenIssuer;
use Firebase\JWT\JWT;
use RuntimeException;

final class JwtTokenIssuer implements TokenIssuer
{
    public function issue(string $username, string $role): array
    {
        $key = (string) config('jwt.signing_key', '');

        if (strlen($key) < 64) {
            throw new RuntimeException('La clave JWT no está configurada correctamente.');
        }

        $now = time();
        $expires = $now + 3600;

        $payload = [
            'sub' => $username,
            'unique_name' => $username,
            'role' => $role,
            'jti' => bin2hex(random_bytes(16)),
            'iat' => $now,
            'exp' => $expires,
        ];

        return [
            'accessToken' => JWT::encode($payload, $key, 'HS256'),
            'expiresAt' => gmdate('Y-m-d\TH:i:s\Z', $expires),
        ];
    }
}