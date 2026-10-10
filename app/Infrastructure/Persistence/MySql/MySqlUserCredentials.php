<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Ports\Outbound\UserCredentials;
use Illuminate\Support\Facades\DB;

final class MySqlUserCredentials implements UserCredentials
{
    public function findByUsername(string $username): ?array
    {
        $normalized = mb_strtolower(trim($username), 'UTF-8');

        if ($normalized === '') {
            return null;
        }

        $row = DB::table('user')
            ->select(['username', 'password_hash', 'role'])
            ->where('username', $normalized)
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'username' => (string) $row->username,
            'passwordHash' => (string) $row->password_hash,
            'role' => (string) $row->role,
        ];
    }
}