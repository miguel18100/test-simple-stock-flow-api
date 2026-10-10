<?php
declare(strict_types=1);
namespace App\Domain\Exception;
final class RepeatedProductException extends BusinessRuleViolation
{
    public function __construct()
    {
        parent::__construct('La venta tiene productos repetidos.');
    }
}