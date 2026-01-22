<?php

use src\Routes\Route;
use src\Http\Controllers\IndexController;
use src\Http\Controllers\UsuarioController;
use src\Http\Middlewares\AuthMiddleware;

// Rotas públicas
Route::get('/', [IndexController::class, 'index']);

// Rotas privadas
Route::get('/api/usuarios', [UsuarioController::class, 'listar'], AuthMiddleware::class);
Route::get('/api/usuario/{uuid}', [UsuarioController::class, 'buscar'], AuthMiddleware::class);
Route::post('/api/criar/usuario', [UsuarioController::class, 'criar'], AuthMiddleware::class);
Route::put('/api/usuario/{uuid}', [UsuarioController::class, 'atualizar'], AuthMiddleware::class);
Route::delete('/api/usuario/{uuid}', [UsuarioController::class, 'deletar'], AuthMiddleware::class);
Route::patch('/api/usuario/{uuid}/desativar', [UsuarioController::class, 'desativar'], AuthMiddleware::class);
Route::patch('/api/usuario/{uuid}/ativar', [UsuarioController::class, 'ativar'], AuthMiddleware::class);

