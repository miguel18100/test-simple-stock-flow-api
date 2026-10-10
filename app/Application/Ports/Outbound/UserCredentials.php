<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface UserCredentials
{
    /**
     * Returns the normalized username, password hash and role,
     * or null when the username does not exist.
     *
     * @return array{username: string, passwordHash: string, role: string}|null
     */
    public function findByUsername(string $username): ?array;
}