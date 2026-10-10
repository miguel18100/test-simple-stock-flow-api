<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use Closure;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class JwtAuthenticate
{
    public function handle(
        Request $request,
        Closure $next,
        ?string $requiredRole = null,
    ): Response {
        $header = $request->header('Authorization', '');

        if (! preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return new JsonResponse([
                'message' => 'Token de autenticación requerido.',
            ], 401);
        }

        $key = (string) config('jwt.signing_key', '');

        if (strlen($key) < 64) {
            return new JsonResponse([
                'message' => 'Autenticación no configurada.',
            ], 500);
        }

        try {
            $claims = JWT::decode($matches[1], new Key($key, 'HS256'));

            $username = $claims->unique_name ?? null;
            $role = $claims->role ?? null;

            if (
                ! is_string($username)
                || $username === ''
                || ! in_array($role, ['admin', 'seller'], true)
            ) {
                return new JsonResponse([
                    'message' => 'Token inválido.',
                ], 401);
            }

            if ($requiredRole !== null && $role !== $requiredRole) {
                return new JsonResponse([
                    'message' => 'No tienes permisos para realizar esta acción.',
                ], 403);
            }

            $request->setUserResolver(
                static fn () => (object) [
                    'username' => $username,
                    'role' => $role,
                ]
            );
        } catch (Throwable) {
            return new JsonResponse([
                'message' => 'Token inválido o expirado.',
            ], 401);
        }

        return $next($request);
    }
}