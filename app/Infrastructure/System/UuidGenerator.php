<?php

declare(strict_types=1);

namespace App\Infrastructure\System;

use App\Application\Ports\Outbound\IdentifierGenerator;
use Illuminate\Support\Str;

final class UuidGenerator implements IdentifierGenerator
{
    public function newUuid(): string
    {
        return (string) Str::uuid();
    }
}