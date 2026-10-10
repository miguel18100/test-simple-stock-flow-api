<?php
declare(strict_types=1);

namespace Tests\Application;

use App\Application\Ports\Inbound\PlaceSaleCommand;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\IdentifierGenerator;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\Ports\Outbound\UserRepository;
use App\Application\UseCase\PlaceSaleService;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\RepeatedProductException;
use App\Domain\Model\Category;
use App\Domain\Model\Product;
use App\Domain\Model\Sale;
use App\Domain\Model\User;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Username;
use PHPUnit\Framework\TestCase;

final class PlaceSaleServiceTest extends TestCase
{
    private const string PRODUCT_ID = '11111111-1111-4111-8111-111111111111';
    private const string CATEGORY_ID = '22222222-2222-4222-8222-222222222222';
    private const string USER_ID = '33333333-3333-4333-8333-333333333333';
    private const string SALE_ID = '44444444-4444-4444-8444-444444444444';

    public function test_empty_sale_is_rejected_before_opening_transaction(): void
    {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(self::never())->method('run');

        $service = new PlaceSaleService(
            $this->createMock(ProductRepository::class),
            $this->createMock(CategoryRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(SaleRepository::class),
            $unitOfWork,
            $this->createMock(Clock::class),
            $this->createMock(IdentifierGenerator::class),
        );

        $this->expectException(EmptySaleException::class);
        $service->execute(new PlaceSaleCommand([], 'vendedor'));
    }

    public function test_repeated_product_is_rejected_before_opening_transaction(): void
    {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(self::never())->method('run');

        $service = new PlaceSaleService(
            $this->createMock(ProductRepository::class),
            $this->createMock(CategoryRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(SaleRepository::class),
            $unitOfWork,
            $this->createMock(Clock::class),
            $this->createMock(IdentifierGenerator::class),
        );

        $lines = [
            ['productId' => self::PRODUCT_ID, 'quantity' => 1],
            ['productId' => self::PRODUCT_ID, 'quantity' => 2],
        ];

        $this->expectException(RepeatedProductException::class);
        $service->execute(new PlaceSaleCommand($lines, 'vendedor'));
    }

    public function test_successful_sale_reduces_stock_and_persists_sale(): void
    {
        $product = new Product(
            ProductId::fromString(self::PRODUCT_ID),
            'Teclado',
            Money::fromDecimal('25000.00'),
            5,
            CategoryId::fromString(self::CATEGORY_ID),
        );

        $category = new Category(
            CategoryId::fromString(self::CATEGORY_ID),
            'Periféricos',
        );

        $user = new User(
            UserId::fromString(self::USER_ID),
            Username::fromString('vendedor'),
            Role::fromString('seller'),
        );

        $products = $this->createMock(ProductRepository::class);
        $products->expects(self::once())
            ->method('findById')
            ->willReturn($product);
        $products->expects(self::once())
            ->method('save')
            ->with($product);

        $categories = $this->createMock(CategoryRepository::class);
        $categories->expects(self::once())
            ->method('findById')
            ->willReturn($category);

        $users = $this->createMock(UserRepository::class);
        $users->expects(self::once())
            ->method('findByUsername')
            ->willReturn($user);

        $sales = $this->createMock(SaleRepository::class);
        $sales->expects(self::once())
            ->method('save')
            ->with(self::callback(
                static function (Sale $sale): bool {
                    return $sale->items() !== []
                        && $sale->total()->amount()->compareTo('50000.00') === 0;
                }
            ));

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(self::once())
            ->method('run')
            ->willReturnCallback(
                static fn (callable $operation): mixed => $operation()
            );

        $clock = $this->createMock(Clock::class);
        $clock->method('now')->willReturn(
            new \DateTimeImmutable('2026-10-09T22:00:00+00:00')
        );

        $identifiers = $this->createMock(IdentifierGenerator::class);
        $identifiers->expects(self::once())
            ->method('newUuid')
            ->willReturn(self::SALE_ID);

        $service = new PlaceSaleService(
            $products,
            $categories,
            $users,
            $sales,
            $unitOfWork,
            $clock,
            $identifiers,
        );

        $result = $service->execute(new PlaceSaleCommand(
            [['productId' => self::PRODUCT_ID, 'quantity' => 2]],
            'vendedor',
        ));

        self::assertSame(self::SALE_ID, $result);
        self::assertSame(3, $product->stock());
    }
}