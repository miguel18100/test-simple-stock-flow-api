<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Ports\Outbound\IdentifierGenerator;
use App\Application\Ports\Outbound\SaleRepository;
use App\Domain\Model\Sale;
use Illuminate\Support\Facades\DB;

final readonly class MySqlSaleRepository implements SaleRepository
{
    public function __construct(
        private IdentifierGenerator $identifiers,
    ) {
    }

    public function save(Sale $sale): void
    {
        DB::table('sale')->insert([
            'id' => $sale->id()->value(),
            'sold_at' => $sale->soldAt()->setTimezone(
                new \DateTimeZone('UTC')
            )->format('Y-m-d H:i:s.u'),
            'sold_by_username' => $sale->soldByUsername()->value(),
            'sold_by_user_id' => $sale->soldByUserId()->value(),
        ]);

        foreach ($sale->items() as $item) {
            DB::table('sale_item')->insert([
                'id' => $this->identifiers->newUuid(),
                'sale_id' => $sale->id()->value(),
                'product_id' => $item->productId()->value(),
                'product_name' => $item->productName(),
                'category_name' => $item->categoryName(),
                'quantity' => $item->quantity()->value(),
                'unit_price' => (string) $item->unitPrice()->amount(),
            ]);
        }
    }
}