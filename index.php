<?php

require_once __DIR__ . '/bootstrap.php';

use src\Http\Request\Request; // Corrigir autoload para Resquest.php
use src\Routes\Router;

// Captura request
$body = file_get_contents('php://input');

$request = new Request(
    body: json_decode($body, true) ?? [],
    query: $_GET,
    headers: getallheaders(),
    method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
    path: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
    rawBody: $body
);

// Inicializa AuthMiddleware com dependência
use src\Routes\Route;
use src\Http\Middlewares\AuthMiddleware;
use src\Services\AuthService;

$authService = new AuthService($usuarioRepo);
$authMiddleware = new AuthMiddleware($authService);

// Registra o middleware no container do Router
$router = new Router([
    AuthMiddleware::class => $authMiddleware,
    src\Http\Controllers\UsuarioController::class => $usuarioController
]);
Route::setRouter($router);
require_once __DIR__ . '/src/Routes/web.php';

// Despacha
$response = $router->dispatchResponse();
$response->Enviar();