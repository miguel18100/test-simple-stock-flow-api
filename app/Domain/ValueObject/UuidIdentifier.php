<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;


abstract readonly class UuidIdentifier
{
    final protected function __construct(
        private string $identifier,
    ) {
        if (! self::isValidUuid($identifier)) {
            throw new \InvalidArgumentException(
                'El identificador debe ser un UUID válido.'
            );
        }
    }

    abstract public static function fromString(string $value): static;

    final public function value(): string
    {
        return $this->identifier;
    }

    private static function isValidUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-8][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/D',
            $value,
        ) === 1;
    }
}