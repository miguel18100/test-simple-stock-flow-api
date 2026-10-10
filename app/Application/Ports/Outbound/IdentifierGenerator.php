<?php
declare(strict_types=1);
namespace App\Application\Ports\Outbound;
interface IdentifierGenerator
{
    public function newUuid(): string;
}