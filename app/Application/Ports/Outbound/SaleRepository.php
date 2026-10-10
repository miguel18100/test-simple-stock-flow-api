<?php
declare(strict_types=1);
namespace App\Application\Ports\Outbound;
use App\Domain\Model\Sale;
interface SaleRepository
{
    public function save(Sale $sale): void;
}