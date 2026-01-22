<?php

namespace src\Routes;

use src\Http\Request\Request;
use src\Http\Response\Response;
use src\Http\Middlewares\MiddlewareInterface;
use src\Exceptions\RouteException;

class Router
{
    private array $routes = [];
    private array $container = [];

    public function __construct(array $container = [])
    {
        $this->container = $container;
    }

    // ======================
    // Registro de Rotas
    // ======================

    public function get(string $path, string $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, string $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, string $handler, array $middlewares = []): void
    {
        $this->add('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, string $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, string|array $handler, string|array|null $middleware = null): void
    {
        $middlewares = $middleware;
        if ($middlewares === null) {
            $middlewares = [];
        } elseif (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        // Permitir handler como array [Classe::class, 'metodo']
        $handlerStr = $handler;
        if (is_array($handler) && count($handler) === 2) {
            $handlerStr = $handler[0] . '@' . $handler[1];
        }

        $this->routes[$method][] = [
            'path' => $this->normalize($path),
            'handler' => $handlerStr,
            'middlewares' => $middlewares
        ];
    }

    // ======================
    // Dispatcher
    // ======================

    /**
     * Executa o dispatch e retorna um objeto Response SEMPRE.
     */
    public function dispatchResponse(): Response
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $request = $this->createRequest();

            $route = $this->match($method, $uri, $request);

            $pipeline = $this->buildPipeline(
                $route['middlewares'],
                $route['handler']
            );

            /** @var Response $response */
            $response = $pipeline($request);
            if (!$response instanceof Response) {
                return Response::json([
                    'error' => 'Resposta inválida do handler.'
                ], 500);
            }
            return $response;

        } catch (RouteException $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], $e->getStatus());
        } catch (\Throwable $e) {
            return Response::json([
                'error' => 'Erro interno no servidor',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mantém compatibilidade: executa e envia a resposta.
     */
    public function dispatch(): void
    {
        $this->dispatchResponse()->Enviar();
    }

    // ======================
    // Pipeline de Middleware
    // ======================

    private function buildPipeline(array $middlewares, string $handler): callable
    {
        $core = function (Request $request) use ($handler) {
            return $this->callHandler($handler, $request);
        };

        foreach (array_reverse($middlewares) as $middlewareClass) {
            $next = $core;

            $core = function (Request $request) use ($middlewareClass, $next) {
                $middleware = $this->resolve($middlewareClass);

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new RouteException(
                        "Middleware inválido: $middlewareClass",
                        500
                    );
                }

                return $middleware->handle($request, $next);
            };
        }

        return $core;
    }

    // ======================
    // Handler
    // ======================

    private function callHandler(string $handler, Request $request): Response
    {
        if (!str_contains($handler, '@')) {
            throw new RouteException("Handler inválido: $handler", 500);
        }

        [$controllerClass, $method] = explode('@', $handler);

        $controller = $this->resolve($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new RouteException(
                "Método $method não encontrado em $controllerClass",
                500
            );
        }

        // Passa o $request e todos os parâmetros da rota (ex: {uuid})
        $params = array_values($request->params ?? []);
        return $controller->$method($request, ...$params);
    }

    // ======================
    // Match de Rotas
    // ======================

    private function match(string $method, string $uri, Request $request): array
    {
        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method])) {
            throw new RouteException("Método não suportado", 405);
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = preg_replace('#\{([\w]+)\}#', '([^/]+)', $route['path']);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                preg_match_all('#\{([\w]+)\}#', $route['path'], $keys);

                $request->params = array_combine($keys[1], $matches) ?: [];

                return $route;
            }
        }

        throw new RouteException("Rota não encontrada", 404);
    }

    // ======================
    // Helpers
    // ======================

    private function normalize(string $path): string
    {
        return '/' . trim($path, '/');
    }

    private function resolve(string $class)
    {
        if (isset($this->container[$class])) {
            return $this->container[$class];
        }

        if (!class_exists($class)) {
            throw new RouteException("Classe não encontrada: $class", 500);
        }

        return new $class();
    }

    private function createRequest(): Request
    {
        $headers = getallheaders();
        $rawBody = file_get_contents('php://input');
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        $body = [];
        if (stripos($contentType, 'application/json') !== false && !empty($rawBody)) {
            $json = json_decode($rawBody, true);
            if (is_array($json)) {
                $body = $json;
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $_POST;
        }
        return new Request(
            body: $body,
            query: $_GET,
            headers: $headers,
            method: $_SERVER['REQUEST_METHOD'],
            path: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            rawBody: $rawBody
        );
    }
}
