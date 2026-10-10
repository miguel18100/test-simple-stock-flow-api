<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

final readonly class PlaceSaleCommand
{
    /**
     * @param list<array{productId: string, quantity: int}> $lines
     */
    public function __construct(
        public array $lines,
        public string $soldByUsername,
    ) {
    }
}