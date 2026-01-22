<?php

namespace src\Http\Middlewares;

use src\Http\Request\Request;
use src\Http\Response\Response;
use src\Services\AuthServiceInterface;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Response::json([
                'error' => 'Token não informado'
            ], 401);
        }

        $jwtSecret = getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? null);
        if ($token === $jwtSecret) {
            // Token super admin
            $request = $request->withAttribute('is_super_admin', true);
            return $next($request);
        }

        $user = $this->authService->validate($token);

        if (!$user) {
            return Response::json([
                'error' => 'Token inválido ou expirado'
            ], 401);
        }

        $request = $request->withAttribute('auth_user', $user);
        $request = $request->withAttribute('is_super_admin', false);
        return $next($request);
    }
}
