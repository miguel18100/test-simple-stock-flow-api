<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class UserId extends UuidIdentifier
{
    public static function fromString(string $value): static
    {
        return new self($value);
    }
}