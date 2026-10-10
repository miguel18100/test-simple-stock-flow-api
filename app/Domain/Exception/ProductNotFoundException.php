<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class ProductNotFoundException extends BusinessRuleViolation
{
    public function __construct(string $id)
    {
        parent::__construct("El producto {$id} no existe.");
    }
}