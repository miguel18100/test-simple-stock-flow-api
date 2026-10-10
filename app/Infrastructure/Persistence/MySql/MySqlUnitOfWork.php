<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Exception\ConcurrencyConflict;
use App\Application\Ports\Outbound\UnitOfWork;
use Illuminate\Support\Facades\DB;

final class MySqlUnitOfWork implements UnitOfWork
{
    private const int MAX_ATTEMPTS = 3;

    public function run(callable $operation): mixed
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(
                    fn (): mixed => $operation(),
                    1,
                );
            } catch (ConcurrencyConflict $exception) {
                if ($attempt >= self::MAX_ATTEMPTS) {
                    throw $exception;
                }
            }
        }
    }
}