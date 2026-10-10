<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UuidIdentifierTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    public function test_product_id_preserves_a_valid_uuid(): void
    {
        self::assertSame(self::UUID, ProductId::fromString(self::UUID)->value());
    }

    public function test_sale_id_preserves_a_valid_uuid(): void
    {
        self::assertSame(self::UUID, SaleId::fromString(self::UUID)->value());
    }

    public function test_category_id_preserves_a_valid_uuid(): void
    {
        self::assertSame(self::UUID, CategoryId::fromString(self::UUID)->value());
    }

    public function test_user_id_preserves_a_valid_uuid(): void
    {
        self::assertSame(self::UUID, UserId::fromString(self::UUID)->value());
    }

    public function test_it_rejects_an_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductId::fromString('not-a-uuid');
    }

    public function test_it_rejects_a_uuid_without_hyphens(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductId::fromString('550e8400e29b41d4a716446655440000');
    }

    public function test_identifier_types_are_distinct(): void
    {
        self::assertNotSame(
            ProductId::fromString(self::UUID)::class,
            SaleId::fromString(self::UUID)::class,
        );
    }
}