<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use App\Application\Ports\Inbound\PlaceSaleCommand;
use App\Application\UseCase\PlaceSaleService;
use App\Domain\Exception\InsufficientStockException;
use App\Infrastructure\Persistence\MySql\MySqlCategoryRepository;
use App\Infrastructure\Persistence\MySql\MySqlProductRepository;
use App\Infrastructure\Persistence\MySql\MySqlSaleRepository;
use App\Infrastructure\Persistence\MySql\MySqlUnitOfWork;
use App\Infrastructure\Persistence\MySql\MySqlUserRepository;
use App\Infrastructure\System\SystemClock;
use App\Infrastructure\System\UuidGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MySqlSalePersistenceTest extends TestCase
{
    private string $userId;
    private string $productId;
    private string $username;
    private string $categoryId = '11111111-1111-4111-8111-111111111111';

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $this->userId = (string) Str::uuid();
        $this->productId = (string) Str::uuid();
        $this->username = 'integration-' . str_replace('-', '', (string) Str::uuid());

        DB::table('user')->insert([
            'id' => $this->userId,
            'username' => $this->username,
            'password_hash' => 'integration-test-hash',
            'role' => 'seller',
        ]);

        DB::table('product')->insert([
            'id' => $this->productId,
            'name' => 'Producto de integración',
            'price' => '12.50',
            'stock' => 10,
            'category_id' => $this->categoryId,
            'image_key' => null,
            'deleted_at' => null,
            'version' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        try {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_sale_persists_and_decreases_mysql_stock(): void
    {
        $saleId = $this->service()->execute(new PlaceSaleCommand([
            ['productId' => $this->productId, 'quantity' => 2],
        ], $this->username));

        $this->assertSame(
            8,
            (int) DB::table('product')
                ->where('id', $this->productId)
                ->value('stock'),
        );

        $sale = DB::table('sale')->where('id', $saleId)->first();

        $this->assertNotNull($sale);
        $this->assertSame($this->userId, $sale->sold_by_user_id);

        $item = DB::table('sale_item')->where('sale_id', $saleId)->first();

        $this->assertNotNull($item);
        $this->assertSame($this->productId, $item->product_id);
        $this->assertSame('Producto de integración', $item->product_name);
        $this->assertSame('General', $item->category_name);
        $this->assertSame(2, (int) $item->quantity);
        $this->assertSame('12.50', (string) $item->unit_price);
    }

    public function test_insufficient_stock_rolls_back_sale_and_stock_change(): void
    {
        DB::table('product')
            ->where('id', $this->productId)
            ->update(['stock' => 1]);

        try {
            $this->service()->execute(new PlaceSaleCommand([
                ['productId' => $this->productId, 'quantity' => 2],
            ], $this->username));

            $this->fail('Se esperaba InsufficientStockException.');
        } catch (InsufficientStockException) {
            $this->assertSame(
                1,
                (int) DB::table('product')
                    ->where('id', $this->productId)
                    ->value('stock'),
            );

            $this->assertSame(
                0,
                DB::table('sale')
                    ->where('sold_by_user_id', $this->userId)
                    ->count(),
            );
        }
    }

    private function service(): PlaceSaleService
    {
        $ids = new UuidGenerator();

        return new PlaceSaleService(
            new MySqlProductRepository(),
            new MySqlCategoryRepository(),
            new MySqlUserRepository(),
            new MySqlSaleRepository($ids),
            new MySqlUnitOfWork(),
            new SystemClock(),
            $ids,
        );
    }
}
