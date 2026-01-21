<?php

namespace src\Services;

use src\Domain\Entities\Usuario;

interface UsuarioServiceInterface
{
    public function emailExiste(string $email): bool;
    public function usernameExiste(string $username): bool;
    public function criar(Usuario $usuario): void;

    public function atualizar(string $uuid, array $dados): void;

    public function buscarPorUuid(string $uuid): ?Usuario;

    /**
     * @return Usuario[]
     */
    public function listar(int $pagina = 1, int $porPagina = 20): array;

    public function desativar(string $uuid): void;

    public function ativar(string $uuid): void;

    public function deletar(string $uuid): void;
}
