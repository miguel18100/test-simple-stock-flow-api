<?php
declare(strict_types=1);
namespace App\Domain\Model;
use App\Domain\ValueObject\CategoryId;
final readonly class Category
{
    public function __construct(
        private CategoryId $id,
        private string $name,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }
    }
    public function id(): CategoryId { return $this->id; }
    public function name(): string { return $this->name; }
}