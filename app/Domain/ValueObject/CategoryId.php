<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class CategoryId extends UuidIdentifier
{
    public static function fromString(string $value): static
    {
        return new self($value);
    }
}