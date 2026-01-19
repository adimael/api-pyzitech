<?php

namespace src\Repositories;

use src\Domain\Entities\Usuario;

interface UsuarioRepositoryInterface
{
    /**
     * Retorna usuários paginados
     * 
     * @return Usuario[]
     */
    public function buscarTodos(int $limite = 100, int $offset = 0): array;

    /**
     * Busca um usuário pelo UUID
     */
    public function buscarPorUuid(string $uuid): ?Usuario;

    /**
     * Busca um usuário pelo username
     */
    public function buscarPorUsername(string $username): ?Usuario;

    /**
     * Busca um usuário pelo e-mail
     */
    public function buscarPorEmail(string $email): ?Usuario;

    /**
     * Verifica se e-mail já existe
     */
    public function emailExiste(string $email, ?string $excluirUuid = null): bool;

    /**
     * Verifica se username já existe
     */
    public function usernameExiste(string $username, ?string $excluirUuid = null): bool;

    /**
     * Salva (cria ou atualiza) um usuário
     */
    public function salvar(Usuario $usuario): void;

    /**
     * Remove um usuário pelo UUID
     */
    public function deletar(string $uuid): void;

    /**
     * Retorna total de usuários
     */
    public function contar(): int;

    /**
     * Busca usuários por nome com paginação
     * 
     * @return Usuario[]
     */
    public function buscarPorNomePaginado(
        string $nome,
        int $pagina = 1,
        int $porPagina = 10
    ): array;
}
