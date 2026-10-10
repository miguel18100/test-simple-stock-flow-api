<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Exception\ConcurrencyConflict;
use App\Application\Ports\Outbound\ProductRepository;
use App\Domain\Model\Product;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use Illuminate\Support\Facades\DB;

final class MySqlProductRepository implements ProductRepository
{
    /** @var array<string, int> */
    private array $loadedVersions = [];

    public function findById(ProductId $id): ?Product
    {
        $row = DB::table('product')
            ->where('id', $id->value())
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->first();

        if ($row === null) {
            unset($this->loadedVersions[$id->value()]);
            return null;
        }

        $this->loadedVersions[$id->value()] = (int) $row->version;

        return new Product(
            ProductId::fromString((string) $row->id),
            (string) $row->name,
            Money::fromDecimal((string) $row->price),
            (int) $row->stock,
            CategoryId::fromString((string) $row->category_id),
            $row->image_key === null ? null : (string) $row->image_key,
        );
    }

    public function save(Product $product): void
    {
        $id = $product->id()->value();

        if (!array_key_exists($id, $this->loadedVersions)) {
            throw new \LogicException(
                'El producto debe cargarse antes de guardarse.'
            );
        }

        $version = $this->loadedVersions[$id];

        $affected = DB::table('product')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->where('version', $version)
            ->update([
                'name' => $product->name(),
                'price' => (string) $product->price()->amount(),
                'stock' => $product->stock(),
                'category_id' => $product->categoryId()->value(),
                'image_key' => $product->imageKey(),
                'version' => $version + 1,
            ]);

        if ($affected !== 1) {
            throw new ConcurrencyConflict();
        }

        $this->loadedVersions[$id] = $version + 1;
    }
}