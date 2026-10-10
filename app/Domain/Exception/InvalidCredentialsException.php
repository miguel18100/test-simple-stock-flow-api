<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class InvalidCredentialsException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('Las credenciales no son válidas.');
    }
}