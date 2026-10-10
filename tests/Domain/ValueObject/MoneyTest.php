<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_it_keeps_two_decimal_places(): void
    {
        $money = Money::fromDecimal('123.45');

        self::assertSame('123.45', (string) $money->amount());
    }

    public function test_it_rounds_half_up_to_two_decimal_places(): void
    {
        $money = Money::fromDecimal('10.125');

        self::assertSame('10.13', (string) $money->amount());
    }

    public function test_it_rounds_down_when_the_third_decimal_is_below_five(): void
    {
        $money = Money::fromDecimal('10.124');

        self::assertSame('10.12', (string) $money->amount());
    }

    public function test_currency_is_cop(): void
    {
        $money = Money::fromDecimal('25.00');

        self::assertSame('COP', $money->currency());
    }

    public function test_it_does_not_use_floating_point_for_amounts(): void
    {
        $money = Money::fromDecimal('999999999999.99');

        self::assertSame('999999999999.99', (string) $money->amount());
    }
}