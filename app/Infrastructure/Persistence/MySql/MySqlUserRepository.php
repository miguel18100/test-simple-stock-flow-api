<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use Illuminate\Support\Facades\DB;

final class MySqlUserRepository implements UserRepository
{
    public function findByUsername(Username $username): ?User
    {
        $row = DB::table('user')
            ->where('username', $username->value())
            ->first();

        if ($row === null) {
            return null;
        }

        return new User(
            UserId::fromString((string) $row->id),
            Username::fromString((string) $row->username),
            Role::fromString((string) $row->role),
        );
    }
}