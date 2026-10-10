<?php
declare(strict_types=1);
namespace App\Application\Ports\Outbound;
use App\Domain\Model\Category;
use App\Domain\ValueObject\CategoryId;
interface CategoryRepository
{
    public function findById(CategoryId $id): ?Category;
}