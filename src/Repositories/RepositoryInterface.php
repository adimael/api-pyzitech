<?php

namespace src\Repositories;

interface RepositoryInterface
{
    /**
     * Busca todos os registros
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function buscarTodos(int $limite = 100, int $offset = 0): array;

    /**
     * Busca um Registro pelo UUID
     * @param string $uuid
     * @return array|null
     */
    public function buscarPorUuid(string $uuid): ?array;

    /**
     * Criar um novo registro
     * @param array $dados
     * @return string UUID do novo registro
     */
    public function criar(array $dados): string;

    /**
     * Atualiza um registro pelo UUID
     * @param string $uuid
     * @param array $dados
     * @return bool
     */
    public function atualizar(string $uuid, array $dados): bool;

    /**
     * Deleta um registro pelo UUID
     * @param string $uuid
     * @return bool
     */
    public function deletar(string $uuid): bool;

    /**
     * Conta o total de registros
     * @return int
     */
    public function contar(): int;

    /**
     * Buscar registros por critérios
     * @param string $coluna
     * @param mixed $valor
     * @return array
     */
    public function buscarPor(string $coluna, $valor): array;

    /**
     * Verifica se um registro existe pelo UUID
     * @param string $uuid
     * @return bool
     */
    public function existe(string $uuid): bool;
}