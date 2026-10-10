<?php
declare(strict_types=1);
namespace App\Domain\Model;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use App\Domain\ValueObject\Quantity;
final class Sale
{
    /** @var list<SaleItem> */
    private array $items = [];
    public function __construct(
        private readonly SaleId $id,
        private readonly \DateTimeImmutable $soldAt,
        private readonly Username $soldByUsername,
        private readonly UserId $soldByUserId,
    ) {}
    public function id(): SaleId { return $this->id; }
    public function soldAt(): \DateTimeImmutable { return $this->soldAt; }
    public function soldByUsername(): Username { return $this->soldByUsername; }
    public function soldByUserId(): UserId { return $this->soldByUserId; }
    /** @return list<SaleItem> */
    public function items(): array { return $this->items; }
    public function addItem(Product $product, Category $category, Quantity $quantity): void
    {
        if ($product->categoryId()->value() !== $category->id()->value()) {
            throw new UnknownCategoryException();
        }
        foreach ($this->items as $item) {
            if ($item->productId()->value() === $product->id()->value()) {
                throw new RepeatedProductException();
            }
        }
        $product->withdraw($quantity);
        $this->items[] = new SaleItem(
            $product->id(),
            $product->name(),
            $category->name(),
            $quantity,
            $product->price(),
        );
    }
    public function ensureConfirmable(): void
    {
        if ($this->items === []) {
            throw new EmptySaleException();
        }
    }
    public function total(): Money
    {
        $total = \Brick\Math\BigDecimal::zero();
        foreach ($this->items as $item) {
            $total = $total->plus($item->subtotal()->amount());
        }
        return Money::fromDecimal((string) $total);
    }
}