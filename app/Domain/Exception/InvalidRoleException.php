<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRoleException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('El rol debe ser admin o seller.');
    }
}