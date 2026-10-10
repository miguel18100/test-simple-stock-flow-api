<?php
declare(strict_types=1);
namespace App\Domain\Model;
use App\Domain\Exception\InsufficientStockException;
use App\Domain\Exception\InvalidPriceException;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
final class Product
{
    private string $name;
    private Money $price;
    private int $stock;
    private CategoryId $categoryId;
    private ?string $imageKey;
    public function __construct(
        private readonly ProductId $id,
        string $name,
        Money $price,
        int $stock,
        CategoryId $categoryId,
        ?string $imageKey = null,
    ) {
        $this->rename($name);
        $this->changePrice($price);
        if ($stock < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo.');
        }
        $this->stock = $stock;
        $this->categoryId = $categoryId;
        $this->attachImage($imageKey);
    }
    public function id(): ProductId { return $this->id; }
    public function name(): string { return $this->name; }
    public function price(): Money { return $this->price; }
    public function stock(): int { return $this->stock; }
    public function categoryId(): CategoryId { return $this->categoryId; }
    public function imageKey(): ?string { return $this->imageKey; }
    public function rename(string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name, 'UTF-8') > 200) {
            throw new \InvalidArgumentException('El nombre del producto debe tener entre 1 y 200 caracteres.');
        }
        $this->name = $name;
    }
    public function changePrice(Money $price): void
    {
        if ($price->amount()->compareTo('0') <= 0) {
            throw new InvalidPriceException();
        }
        $this->price = $price;
    }
    public function withdraw(Quantity $quantity): void
    {
        if ($quantity->value() > $this->stock) {
            throw new InsufficientStockException($this->name, $this->stock, $quantity->value());
        }
        $this->stock -= $quantity->value();
    }
    public function restock(Quantity $quantity): void
    {
        $this->stock += $quantity->value();
    }
    public function setCategory(CategoryId $categoryId): void
    {
        $this->categoryId = $categoryId;
    }
    public function attachImage(?string $imageKey): void
    {
        $imageKey = $imageKey === null ? null : trim($imageKey);
        $this->imageKey = $imageKey === '' ? null : $imageKey;
    }
}