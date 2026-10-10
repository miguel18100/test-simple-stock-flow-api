<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Ports\Outbound\TokenIssuer;
use App\Application\Ports\Outbound\UserCredentials;
use App\Application\Ports\Inbound\Login;

final readonly class LoginService implements Login
{
    public function __construct(
        private UserCredentials $credentials,
        private TokenIssuer $tokens,
    ) {
    }

    /** @return array{accessToken: string, expiresAt: string, username: string, role: string}|null */
    public function execute(string $username, string $password): ?array
    {
        $username = mb_strtolower(trim($username), 'UTF-8');

        if ($username === '' || $password === '') {
            return null;
        }

        $record = $this->credentials->findByUsername($username);

        if ($record === null || ! password_verify($password, $record['passwordHash'])) {
            return null;
        }

        $token = $this->tokens->issue($record['username'], $record['role']);

        return [
            ...$token,
            'username' => $record['username'],
            'role' => $record['role'],
        ];
    }
}