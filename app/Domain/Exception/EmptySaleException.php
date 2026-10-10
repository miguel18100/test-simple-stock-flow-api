<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class EmptySaleException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('La venta debe tener al menos un ítem.');
    }
}