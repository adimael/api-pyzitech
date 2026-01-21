<?php

namespace src\Services;

use src\Domain\Entities\Usuario;
use src\Repositories\UsuarioRepositoryInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarios
    ) {}

    public function validate(string $token): ?Usuario
    {
        $jwtSecret = getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? null);
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        list($headerB64, $payloadB64, $signatureB64) = $parts;
        $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
        $signature = base64_decode(strtr($signatureB64, '-_', '+/'));

        if (!$header || !$payload || !$signature) {
            return null;
        }

        // Verifica algoritmo
        if (($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        // Verifica expiração
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        // Verifica assinatura
        $base = $headerB64 . '.' . $payloadB64;
        $expected = hash_hmac('sha256', $base, $jwtSecret, true);
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        // Verifica UUID
        $uuid = $payload['uuid'] ?? null;
        if (!$uuid || !preg_match('/^[0-9a-fA-F-]{36}$/', $uuid)) {
            return null;
        }

        return $this->usuarios->buscarPorUuid($uuid);
    }
}
