<?php
declare(strict_types=1);
namespace App\Application\Ports\Outbound;
use App\Domain\Model\Product;
use App\Domain\ValueObject\ProductId;
interface ProductRepository
{
    public function findById(ProductId $id): ?Product;
    public function save(Product $product): void;
}