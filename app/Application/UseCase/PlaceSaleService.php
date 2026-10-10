<?php
declare(strict_types=1);
namespace App\Application\UseCase;
use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Inbound\PlaceSaleCommand;
use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\IdentifierGenerator;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Exception\EmptySaleException;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Exception\UnknownCategoryException;
use App\Domain\Model\Sale;
use App\Domain\ValueObject\CategoryId;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\Quantity;
use App\Domain\ValueObject\SaleId;
use App\Domain\ValueObject\Username;
final readonly class PlaceSaleService implements PlaceSale
{
    public function __construct(
        private ProductRepository $products,
        private CategoryRepository $categories,
        private UserRepository $users,
        private SaleRepository $sales,
        private UnitOfWork $unitOfWork,
        private Clock $clock,
        private IdentifierGenerator $identifiers,
    ) {}

    public function execute(PlaceSaleCommand $command): string
    {
        if ($command->lines === []) {
            throw new EmptySaleException();
        }

        $seen = [];
        foreach ($command->lines as $line) {
            $id = strtolower((string) ($line['productId'] ?? ''));
            if (isset($seen[$id])) {
                throw new \App\Domain\Exception\RepeatedProductException();
            }
            $seen[$id] = true;
        }

        return $this->unitOfWork->run(function () use ($command): string {
            $username = Username::fromString($command->soldByUsername);
            $user = $this->users->findByUsername($username);
            if ($user === null) {
                throw new InvalidCredentialsException();
            }

            $sale = new Sale(
                SaleId::fromString($this->identifiers->newUuid()),
                $this->clock->now(),
                $user->username(),
                $user->id(),
            );

            $productsToSave = [];
            foreach ($command->lines as $line) {
                $productId = ProductId::fromString($line['productId']);
                $product = $this->products->findById($productId);
                if ($product === null) {
                    throw new ProductNotFoundException($line['productId']);
                }

                $quantity = Quantity::fromInt($line['quantity']);
                $category = $this->categories->findById($product->categoryId());
                if ($category === null) {
                    throw new UnknownCategoryException();
                }

                $sale->addItem($product, $category, $quantity);
                $productsToSave[] = $product;
            }

            $sale->ensureConfirmable();

            foreach ($productsToSave as $product) {
                $this->products->save($product);
            }
            $this->sales->save($sale);

            return $sale->id()->value();
        });
    }
}