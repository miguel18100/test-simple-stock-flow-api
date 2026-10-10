<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final readonly class Money
{
    private const int SCALE = 2;
    private const string CURRENCY = 'COP';

    private function __construct(
        private BigDecimal $value,
    ) {
    }

    public static function fromDecimal(string $amount): self
    {
        $value = BigDecimal::of($amount)->toScale(
            self::SCALE,
            RoundingMode::HalfUp,
        );

        return new self($value);
    }

    public function amount(): BigDecimal
    {
        return $this->value;
    }

    public function currency(): string
    {
        return self::CURRENCY;
    }
}