<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantityException;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function test_it_accepts_a_positive_integer(): void
    {
        $quantity = Quantity::fromInt(5);

        self::assertSame(5, $quantity->value());
    }

    public function test_it_accepts_one_as_the_minimum_quantity(): void
    {
        $quantity = Quantity::fromInt(1);

        self::assertSame(1, $quantity->value());
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::fromInt(0);
    }

    public function test_it_rejects_negative_quantities(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::fromInt(-1);
    }
}