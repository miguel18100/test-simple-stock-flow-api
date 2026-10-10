<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantityException;

final readonly class Quantity
{
    private function __construct(
        private int $quantity,
    ) {
    }

    public static function fromInt(int $quantity): self
    {
        if ($quantity <= 0) {
            throw new InvalidQuantityException();
        }

        return new self($quantity);
    }

    public function value(): int
    {
        return $this->quantity;
    }
}