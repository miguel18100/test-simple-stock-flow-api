<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\Username;
use PHPUnit\Framework\TestCase;

final class UsernameTest extends TestCase
{
    public function test_it_trims_whitespace(): void
    {
        self::assertSame('miguel', Username::fromString('  miguel  ')->value());
    }

    public function test_it_normalizes_to_lowercase(): void
    {
        self::assertSame('miguel', Username::fromString('MIGUEL')->value());
    }

    public function test_it_trims_and_normalizes_together(): void
    {
        self::assertSame('miguel.perez', Username::fromString('  Miguel.Perez  ')->value());
    }

    public function test_it_rejects_an_empty_username(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Username::fromString('   ');
    }
}