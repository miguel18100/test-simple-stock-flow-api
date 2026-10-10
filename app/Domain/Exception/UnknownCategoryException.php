<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class UnknownCategoryException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('La categoría del producto no coincide.');
    }
}