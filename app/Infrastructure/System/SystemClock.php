<?php

declare(strict_types=1);

namespace App\Infrastructure\System;

use App\Application\Ports\Outbound\Clock;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}