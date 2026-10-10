<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidRoleException;
use App\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function test_it_accepts_admin(): void
    {
        self::assertSame('admin', Role::fromString('admin')->value());
    }

    public function test_it_accepts_seller(): void
    {
        self::assertSame('seller', Role::fromString('seller')->value());
    }

    public function test_it_rejects_unknown_roles(): void
    {
        $this->expectException(InvalidRoleException::class);

        Role::fromString('manager');
    }

    public function test_it_rejects_uppercase_roles(): void
    {
        $this->expectException(InvalidRoleException::class);

        Role::fromString('ADMIN');
    }
}