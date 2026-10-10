<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

interface Login
{
    /** @return array{accessToken: string, expiresAt: string, username: string, role: string}|null */
    public function execute(string $username, string $password): ?array;
}