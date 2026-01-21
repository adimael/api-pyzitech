<?php

namespace src\Services;

use src\Domain\Entities\Usuario;
use src\Repositories\UsuarioRepositoryInterface;
use DomainException;

class UsuarioService implements UsuarioServiceInterface
{
    public function emailExiste(string $email): bool
    {
        return $this->repository->emailExiste($email);
    }

    public function usernameExiste(string $username): bool
    {
        return $this->repository->usernameExiste($username);
    }
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

    public function atualizar(string $uuid, array $data): void
    {
        try {
            $usuario = $this->repository->buscarPorUuid($uuid);
            if (!$usuario) {
                throw new DomainException('Usuário não encontrado.');
            }

            $camposPermitidos = [
                'nome_completo', 'username', 'email', 'senha', 'url_avatar', 'url_capa', 'biografia', 'nivel_acesso'
            ];
            $camposInvalidos = array_diff(array_keys($data), $camposPermitidos);
            if (!empty($camposInvalidos)) {
                throw new DomainException('Campos inválidos no update: ' . implode(', ', $camposInvalidos));
            }

            // Atualiza campos se enviados
            if (isset($data['nome_completo'])) {
                $usuario->setNomeCompleto($data['nome_completo']);
            }
            if (isset($data['username'])) {
                $usuario->setUsername($data['username']);
            }
            if (isset($data['email'])) {
                $usuario->setEmail($data['email']);
            }
            if (isset($data['senha'])) {
                $usuario->alterarSenha($data['senha']);
            }
            if (isset($data['url_avatar'])) {
                $usuario->setUrlAvatar($data['url_avatar']);
            }
            if (isset($data['url_capa'])) {
                $usuario->setUrlCapa($data['url_capa']);
            }
            if (isset($data['biografia'])) {
                $usuario->setBiografia($data['biografia']);
            }
            if (isset($data['nivel_acesso'])) {
                $usuario->promoverPara($data['nivel_acesso']);
            }
            $usuario->setAtualizadoEm(new \DateTimeImmutable());

            // Valida unicidade
            if ($this->repository->emailExiste($usuario->getEmail(), $uuid)) {
                throw new DomainException('E-mail já cadastrado.');
            }
            if ($this->repository->usernameExiste($usuario->getUsername(), $uuid)) {
                throw new DomainException('Username já cadastrado.');
            }
            $this->repository->salvar($usuario);
        } catch (\RuntimeException $e) {
            // Erro de persistência (ex: nenhuma linha afetada)
            throw new DomainException($e->getMessage(), 500, $e);
        } catch (\Throwable $e) {
            throw new DomainException('Erro inesperado ao atualizar usuário', 500, $e);
        }
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

    public function deletar(string $uuid): void
    {
        $usuario = $this->repository->buscarPorUuid($uuid);
        if (!$usuario) {
            throw new DomainException('Usuário não encontrado.');
        }
        $this->repository->deletar($uuid);
    }
}
