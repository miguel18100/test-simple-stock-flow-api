<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Ports\Outbound\CategoryRepository;
use App\Application\Ports\Outbound\Clock;
use App\Application\Ports\Outbound\IdentifierGenerator;
use App\Application\Ports\Outbound\ProductRepository;
use App\Application\Ports\Outbound\SaleRepository;
use App\Application\Ports\Outbound\UnitOfWork;
use App\Application\Ports\Outbound\UserRepository;
use App\Application\Ports\Outbound\UserCredentials;
use App\Application\Ports\Outbound\TokenIssuer;
use App\Application\UseCase\PlaceSaleService;
use App\Application\UseCase\LoginService;
use App\Infrastructure\Persistence\MySql\MySqlCategoryRepository;
use App\Infrastructure\Persistence\MySql\MySqlProductRepository;
use App\Infrastructure\Persistence\MySql\MySqlSaleRepository;
use App\Infrastructure\Persistence\MySql\MySqlUnitOfWork;
use App\Infrastructure\Persistence\MySql\MySqlUserRepository;
use App\Infrastructure\Persistence\MySql\MySqlUserCredentials;
use App\Infrastructure\System\SystemClock;
use App\Infrastructure\System\UuidGenerator;
use App\Infrastructure\System\JwtTokenIssuer;
use Illuminate\Support\ServiceProvider;

final class StockFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, MySqlProductRepository::class);
        $this->app->bind(CategoryRepository::class, MySqlCategoryRepository::class);
        $this->app->bind(UserRepository::class, MySqlUserRepository::class);
        $this->app->bind(UserCredentials::class, MySqlUserCredentials::class);
        $this->app->bind(TokenIssuer::class, JwtTokenIssuer::class);
        $this->app->bind(LoginService::class);
        $this->app->bind(\App\Application\Ports\Inbound\Login::class, LoginService::class);
        $this->app->bind(SaleRepository::class, MySqlSaleRepository::class);
        $this->app->bind(UnitOfWork::class, MySqlUnitOfWork::class);
        $this->app->bind(Clock::class, SystemClock::class);
        $this->app->bind(IdentifierGenerator::class, UuidGenerator::class);
    }
}