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
     * POST /criar/usuario
     */
    public function criar($request): Response
    {
        try {
            $data = $request->body ?? [];
            // Validação de campos obrigatórios
            foreach (['nome_completo', 'username', 'email', 'senha'] as $campo) {
                if (empty($data[$campo])) {
                    return Response::json([
                        'status' => 'error',
                        'message' => "Campo obrigatório não informado: $campo"
                    ], 400);
                }
            }
            // Validação de unicidade antes de criar o objeto
            if ($this->service->emailExiste($data['email'])) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'E-mail já cadastrado.'
                ], 400);
            }
            if ($this->service->usernameExiste($data['username'])) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Username já cadastrado.'
                ], 400);
            }
            $usuario = Usuario::registrar(
                nomeCompleto: $data['nome_completo'],
                username: $data['username'],
                email: $data['email'],
                senha: $data['senha'],
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
            $debug = getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? 'false');
            $details = ($debug === 'true') ? $e->getMessage() : 'Erro interno no servidor';
            return Response::json([
                'status' => 'error',
                'message' => 'Erro interno no servidor',
                'details' => $details
            ], 500);
        }
    }

    /**
     * GET /usuarios
     */
    public function listar($request, int $pagina = 1, int $porPagina = 10): Response
    {
        $isSuperAdmin = $request->attribute('is_super_admin', false);
        $authUser = $request->attribute('auth_user');

        if ($isSuperAdmin) {
            $usuarios = $this->service->listar($pagina, $porPagina);
            $data = array_map(fn($u) => [
                'uuid' => $u->getUuid()->toString(),
                'nome' => $u->getNomeCompleto(),
                'username' => $u->getUsername(),
                'email' => $u->getEmail(),
                'ativo' => $u->isAtivo()
            ], $usuarios);
            return Response::json($data);
        } else {
            // Usuário comum só vê seus próprios dados
            if (!$authUser) {
                return Response::json(['error' => 'Não autenticado'], 401);
            }
            $data = [[
                'uuid' => $authUser->getUuid()->toString(),
                'nome' => $authUser->getNomeCompleto(),
                'username' => $authUser->getUsername(),
                'email' => $authUser->getEmail(),
                'ativo' => $authUser->isAtivo()
            ]];
            return Response::json($data);
        }
    }

    /**
     * PUT /usuario/{uuid}
     */
    public function atualizar($request, string $uuid): Response
    {
        try {
            $data = $request->body ?? [];
            $this->service->atualizar($uuid, $data);
            return Response::json([
                'status' => 'success',
                'message' => 'Usuário atualizado com sucesso'
            ]);
        } catch (DomainException $e) {
            $debug = getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? 'false');
            $details = ($debug === 'true') ? $e->getMessage() : null;
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'details' => $details
            ], $e->getCode() === 500 ? 500 : 400);
        } catch (Throwable $e) {
            $debug = getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? 'false');
            $details = ($debug === 'true') ? $e->getMessage() : null;
            return Response::json([
                'status' => 'error',
                'message' => 'Erro interno no servidor',
                'details' => $details
            ], 500);
        }
    }

    /**
     * GET /usuario/{uuid}
     */
    public function buscar($request): Response
    {
        $isSuperAdmin = $request->attribute('is_super_admin', false);
        $authUser = $request->attribute('auth_user');
        $uuid = $request->param('uuid');

        if ($isSuperAdmin) {
            if (!$uuid) {
                return Response::json(['error' => 'UUID não informado'], 400);
            }
            $usuario = $this->service->buscarPorUuid($uuid);
        } else {
            if (!$authUser) {
                return Response::json(['error' => 'Não autenticado'], 401);
            }
            $usuario = $this->service->buscarPorUuid($authUser->getUuid()->toString());
        }

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
     * DELETE /usuario/{uuid}
     */
    public function deletar($request): Response
    {
        $isSuperAdmin = $request->attribute('is_super_admin', false);
        $authUser = $request->attribute('auth_user');
        $uuid = $request->param('uuid');

        if ($isSuperAdmin) {
            if (!$uuid) {
                return Response::json(['error' => 'UUID não informado'], 400);
            }
            try {
                $this->service->deletar($uuid);
                return Response::json([
                    'status' => 'success',
                    'message' => 'Usuário excluído com sucesso (super admin)'
                ]);
            } catch (DomainException $e) {
                return Response::json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 404);
            }
        }

        if (!$authUser) {
            return Response::json(['error' => 'Não autenticado'], 401);
        }
        try {
            $this->service->deletar($authUser->getUuid()->toString());
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
     * PATCH /usuario/{uuid}/desativar
     */
    public function desativar($request): Response
    {
        $isSuperAdmin = $request->attribute('is_super_admin', false);
        $authUser = $request->attribute('auth_user');
        $uuid = $request->param('uuid');

        if ($isSuperAdmin) {
            if (!$uuid) {
                return Response::json(['error' => 'UUID não informado'], 400);
            }
            try {
                $this->service->desativar($uuid);
                return Response::json([
                    'status' => 'success',
                    'message' => 'Usuário desativado com sucesso (super admin)'
                ]);
            } catch (DomainException $e) {
                return Response::json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 404);
            }
        }

        if (!$authUser) {
            return Response::json(['error' => 'Não autenticado'], 401);
        }
        try {
            $this->service->desativar($authUser->getUuid()->toString());
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
     * PATCH /usuario/{uuid}/ativar
     */
    public function ativar($request): Response
    {
        $isSuperAdmin = $request->attribute('is_super_admin', false);
        $authUser = $request->attribute('auth_user');
        $uuid = $request->param('uuid');

        if ($isSuperAdmin) {
            if (!$uuid) {
                return Response::json(['error' => 'UUID não informado'], 400);
            }
            try {
                $this->service->ativar($uuid);
                return Response::json([
                    'status' => 'success',
                    'message' => 'Usuário ativado com sucesso (super admin)'
                ]);
            } catch (DomainException $e) {
                return Response::json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 404);
            }
        }

        if (!$authUser) {
            return Response::json(['error' => 'Não autenticado'], 401);
        }
        try {
            $this->service->ativar($authUser->getUuid()->toString());
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
