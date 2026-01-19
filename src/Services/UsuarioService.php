<?php

namespace src\Services;

use src\Domain\Entities\Usuario;
use src\Repositories\UsuarioRepositoryInterface;
use DomainException;

class UsuarioService implements UsuarioServiceInterface
{
    public function __construct(
        private UsuarioRepositoryInterface $repository
    ) {}

    public function criar(Usuario $usuario): void
    {
        if ($this->repository->emailExiste($usuario->getEmail())) {
            throw new DomainException('E-mail já cadastrado.');
        }
        if ($this->repository->usernameExiste($usuario->getUsername())) {
            throw new DomainException('Username já cadastrado.');
        }
        $this->repository->salvar($usuario);
    }

    public function atualizar(Usuario $usuario): void
    {
        $uuid = $usuario->getUuid()->toString();
        if ($this->repository->emailExiste($usuario->getEmail(), $uuid)) {
            throw new DomainException('E-mail já cadastrado.');
        }
        if ($this->repository->usernameExiste($usuario->getUsername(), $uuid)) {
            throw new DomainException('Username já cadastrado.');
        }
        $this->repository->salvar($usuario);
    }

    public function buscarPorUuid(string $uuid): ?Usuario
    {
        return $this->repository->buscarPorUuid($uuid);
    }

    public function listar(int $pagina = 1, int $porPagina = 20): array
    {
        // Exemplo: busca paginada por nome vazio (todos)
        return $this->repository->buscarPorNomePaginado('', $pagina, $porPagina);
    }

    public function desativar(string $uuid): void
    {
        $usuario = $this->repository->buscarPorUuid($uuid);
        if (!$usuario) {
            throw new DomainException('Usuário não encontrado.');
        }
        $usuario->desativar();
        $this->repository->salvar($usuario);
    }

    public function ativar(string $uuid): void
    {
        $usuario = $this->repository->buscarPorUuid($uuid);

        if (!$usuario) {
            throw new DomainException('Usuário não encontrado.');
        }

        $usuario->ativar();
        $this->repository->salvar($usuario);
    }
}
