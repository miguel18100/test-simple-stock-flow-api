<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class ConcurrencyConflict extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Conflicto de concurrencia al actualizar el producto.');
    }
}