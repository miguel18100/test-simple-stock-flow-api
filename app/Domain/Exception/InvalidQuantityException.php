<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidQuantityException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('La cantidad debe ser un entero mayor que cero.');
    }
}