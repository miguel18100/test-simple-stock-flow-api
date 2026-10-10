<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;


final readonly class Username
{
    private const int MAX_LENGTH = 120;

    private function __construct(
        private string $username,
    ) {
    }

    public static function fromString(string $value): self
    {
        $username = mb_strtolower(trim($value), 'UTF-8');

        if ($username === '') {
            throw new \InvalidArgumentException(
                'El nombre de usuario no puede estar vacío.'
            );
        }

        if (mb_strlen($username, 'UTF-8') > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                'El nombre de usuario no puede superar los 120 caracteres.'
            );
        }

        return new self($username);
    }

    public function value(): string
    {
        return $this->username;
    }
}