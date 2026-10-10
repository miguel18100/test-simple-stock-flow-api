<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class InsufficientStockException extends BusinessRuleViolation
{
    public function __construct(string $name, int $available, int $requested)
    {
        parent::__construct("Stock insuficiente para '{$name}': disponible {$available}, solicitado {$requested}.");
    }
}