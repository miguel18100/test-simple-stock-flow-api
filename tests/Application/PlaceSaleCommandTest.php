<?php

declare(strict_types=1);

namespace Tests\Application;

use App\Application\Ports\Inbound\PlaceSaleCommand;
use PHPUnit\Framework\TestCase;

final class PlaceSaleCommandTest extends TestCase
{
    public function test_it_preserves_sale_lines_and_seller_username(): void
    {
        $lines = [
            [
                'productId' => '550e8400-e29b-41d4-a716-446655440000',
                'quantity' => 2,
            ],
        ];

        $command = new PlaceSaleCommand($lines, 'seller@example.com');

        self::assertSame($lines, $command->lines);
        self::assertSame('seller@example.com', $command->soldByUsername);
    }

    public function test_it_accepts_empty_lines_for_domain_validation(): void
    {
        $command = new PlaceSaleCommand([], 'seller@example.com');

        self::assertSame([], $command->lines);
    }
}