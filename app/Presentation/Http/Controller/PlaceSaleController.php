<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Ports\Inbound\PlaceSale;
use App\Application\Ports\Inbound\PlaceSaleCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlaceSaleController
{
    public function __construct(
        private readonly PlaceSale $placeSale,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lines' => ['present', 'array'],
            'lines.*.productId' => ['required', 'uuid'],
            'lines.*.quantity' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $username = $user?->username;

        abort_unless(
            is_string($username) && $username !== '',
            401
        );

        $saleId = $this->placeSale->execute(
            new PlaceSaleCommand(
                $validated['lines'],
                $username,
            )
        );

        return response()->json(
            ['id' => $saleId],
            201,
            ['Location' => '/api/sales/' . $saleId],
        );
    }
}