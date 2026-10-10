<?php
declare(strict_types=1);
namespace App\Domain\Model;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
final readonly class User
{
    public function __construct(
        private UserId $id,
        private Username $username,
        private Role $role,
    ) {}
    public function id(): UserId { return $this->id; }
    public function username(): Username { return $this->username; }
    public function role(): Role { return $this->role; }
}