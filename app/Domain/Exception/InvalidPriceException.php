<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class InvalidPriceException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('El precio debe ser mayor a cero.');
    }
}