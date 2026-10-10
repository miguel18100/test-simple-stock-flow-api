<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface TokenIssuer
{
    /** @return array{accessToken: string, expiresAt: string} */
    public function issue(string $username, string $role): array;
}