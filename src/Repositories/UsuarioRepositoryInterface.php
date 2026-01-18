<?php

namespace src\Repositories;

use src\Domain\Entities\Usuario;

interface UsuarioRepositoryInterface
{
    /**
     * Busca todos os registros
     * @param int $limite
     * @param int $offset
     * @return Usuario[]
     */
    public function buscarTodos(int $limite = 100, int $offset = 0): array;

    /**
     * Busca um Registro pelo UUID
     * @param string $uuid
     * @return Usuario|null
     */
    public function buscarPorUuid(string $uuid): ?Usuario;

    /**
     * Buscar por username
     * @param string $username
     * @return Usuario|null
     */
    public function buscarPorUsername(string $username): ?Usuario;

    /**
     * Buscar por email
     * @param string $email
     * @return Usuario|null
     */
    public function buscarPorEmail(string $email): ?Usuario;

    /**
     * Criar um novo registro
     * @param Usuario $usuario
     * @return void
     */
    public function salvar(Usuario $usuario): void;

    /**
     * Deleta um registro pelo UUID
     * @param string $uuid
     * @return void
     */
    public function deletar(string $uuid): void;

    /**
     * Conta o total de registros
     * @return int
     */
    public function contar(): int;

    /**
     * Verifica se um registro existe pelo UUID
     * @param string $uuid
     * @return Usuario[]
     */
    public function buscarTodosPor(string $coluna, mixed $valor): array;

}