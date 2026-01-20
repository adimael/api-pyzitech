<?php

namespace src\Http\Controllers;

use src\Services\UsuarioServiceInterface;
use src\Domain\Entities\Usuario;
use src\Http\Response\Response;
use DomainException;
use Throwable;

class UsuarioController
{
    public function __construct(
        private UsuarioServiceInterface $service
    ) {}

    /**
     * POST /usuarios
     */
    public function criar(array $data): Response
    {
        try {
            $usuario = Usuario::registrar(
                nomeCompleto: $data['nome_completo'] ?? '',
                username: $data['username'] ?? '',
                email: $data['email'] ?? '',
                senha: $data['senha'] ?? '',
                urlAvatar: $data['url_avatar'] ?? null,
                urlCapa: $data['url_capa'] ?? null,
                biografia: $data['biografia'] ?? null,
                nivelAcesso: $data['nivel_acesso'] ?? 'usuario'
            );

            $this->service->criar($usuario);

            return Response::json([
                'status' => 'success',
                'uuid' => $usuario->getUuid()->toString(),
                'message' => 'Usuário criado com sucesso'
            ], 201);

        } catch (DomainException $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Erro interno no servidor'
            ], 500);
        }
    }

    /**
     * GET /usuarios
     */
    public function listar(int $pagina = 1, int $porPagina = 10): Response
    {
        $usuarios = $this->service->listar($pagina, $porPagina);

        $data = array_map(fn($u) => [
            'uuid' => $u->getUuid()->toString(),
            'nome' => $u->getNomeCompleto(),
            'username' => $u->getUsername(),
            'email' => $u->getEmail(),
            'ativo' => $u->isAtivo()
        ], $usuarios);

        return Response::json($data);
    }

    /**
     * GET /usuarios/{uuid}
     */
    public function buscar(string $uuid): Response
    {
        $usuario = $this->service->buscarPorUuid($uuid);

        if (!$usuario) {
            return Response::json([
                'status' => 'error',
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        return Response::json([
            'uuid' => $usuario->getUuid()->toString(),
            'nome_completo' => $usuario->getNomeCompleto(),
            'username' => $usuario->getUsername(),
            'email' => $usuario->getEmail(),
            'ativo' => $usuario->isAtivo(),
            'nivel_acesso' => $usuario->getNivelAcesso(),
            'criado_em' => $usuario->getCriadoEm()->format('c')
        ]);
    }

    /**
     * DELETE /usuarios/{uuid}
     */
    public function deletar(string $uuid): Response
    {
        try {
            $this->service->deletar($uuid);

            return Response::json([
                'status' => 'success',
                'message' => 'Usuário excluído com sucesso'
            ]);
        } catch (DomainException $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * PATCH /usuarios/{uuid}/desativar
     */
    public function desativar(string $uuid): Response
    {
        try {
            $this->service->desativar($uuid);

            return Response::json([
                'status' => 'success',
                'message' => 'Usuário desativado com sucesso'
            ]);
        } catch (DomainException $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * PATCH /usuarios/{uuid}/ativar
     */
    public function ativar(string $uuid): Response
    {
        try {
            $this->service->ativar($uuid);

            return Response::json([
                'status' => 'success',
                'message' => 'Usuário ativado com sucesso'
            ]);
        } catch (DomainException $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
