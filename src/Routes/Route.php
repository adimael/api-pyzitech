<?php

namespace src\Routes;

class Route
{
        public static function patch(string $path, string|array $handler, string|array|null $middleware = null): void 
        {
            self::ensureRouter()
                ->add('PATCH', $path, $handler, $middleware);
        }
    private static ?Router $router = null;

    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }

    private static function ensureRouter(): Router
    {
        if (!self::$router) {
            throw new \RuntimeException(
                'Router não configurado. Chame Route::setRouter() antes de usar as rotas.'
            );
        }

        return self::$router;
    }

    public static function get(string $path, string|array $handler, string|array|null $middleware = null): void 
    {
        self::ensureRouter()
            ->add('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, string|array $handler, string|array|null $middleware = null): void 
    {
        self::ensureRouter()
            ->add('POST', $path, $handler, $middleware);
    }

    public static function put(string $path, string|array $handler, string|array|null $middleware = null): void 
    {
        self::ensureRouter()
            ->add('PUT', $path, $handler, $middleware);
    }

    public static function delete(string $path, string|array $handler, string|array|null $middleware = null): void 
    {
        self::ensureRouter()
            ->add('DELETE', $path, $handler, $middleware);
    }
}
