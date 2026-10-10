<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidRoleException;

final readonly class Role
{
    private const string ADMIN = 'admin';
    private const string SELLER = 'seller';

    private function __construct(
        private string $role,
    ) {
    }

    public static function fromString(string $value): self
    {
        if (! in_array($value, [self::ADMIN, self::SELLER], true)) {
            throw new InvalidRoleException();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->role === self::SELLER;
    }
}