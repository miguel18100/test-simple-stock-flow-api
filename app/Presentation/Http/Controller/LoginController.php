<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Ports\Inbound\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LoginController
{
    public function __construct(
        private readonly Login $login,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->login->execute(
            $validated['username'],
            $validated['password'],
        );

        if ($result === null) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos.',
            ], 422);
        }

        return response()->json($result);
    }
}