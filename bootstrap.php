<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Inicialização de timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Inicialização de conexões e dependências globais
use src\Database\PostgreSQL\Conexao as PostgresConexao;
use src\Repositories\UsuarioRepository;
use src\Services\UsuarioService;
use src\Http\Controllers\UsuarioController;
use src\Http\Controllers\IndexController;

$pdo = PostgresConexao::conectar();
$usuarioRepo = new UsuarioRepository($pdo);
$usuarioService = new UsuarioService($usuarioRepo);
$usuarioController = new UsuarioController($usuarioService);
$indexController = new IndexController();