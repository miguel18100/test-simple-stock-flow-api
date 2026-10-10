<?php
declare(strict_types=1);
namespace App\Domain\Model;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
final readonly class SaleItem
{
    public function __construct(
        private ProductId $productId,
        private string $productName,
        private string $categoryName,
        private Quantity $quantity,
        private Money $unitPrice,
    ) {
        if (trim($productName) === '' || trim($categoryName) === '') {
            throw new \InvalidArgumentException('El producto y la categoría son obligatorios.');
        }
        if ($unitPrice->amount()->compareTo('0') <= 0) {
            throw new \InvalidArgumentException('El precio unitario debe ser mayor a cero.');
        }
    }
    public function productId(): ProductId { return $this->productId; }
    public function productName(): string { return $this->productName; }
    public function categoryName(): string { return $this->categoryName; }
    public function quantity(): Quantity { return $this->quantity; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function subtotal(): Money
    {
        return Money::fromDecimal(
            (string) $this->unitPrice->amount()->multipliedBy((string) $this->quantity->value())
        );
    }
}