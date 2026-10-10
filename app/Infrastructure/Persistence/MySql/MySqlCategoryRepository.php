<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use App\Application\Ports\Outbound\CategoryRepository;
use App\Domain\Model\Category;
use App\Domain\ValueObject\CategoryId;
use Illuminate\Support\Facades\DB;

final class MySqlCategoryRepository implements CategoryRepository
{
    public function findById(CategoryId $id): ?Category
    {
        $row = DB::table('category')->where('id', $id->value())->first();

        if ($row === null) {
            return null;
        }

        return new Category(
            CategoryId::fromString((string) $row->id),
            (string) $row->name,
        );
    }
}