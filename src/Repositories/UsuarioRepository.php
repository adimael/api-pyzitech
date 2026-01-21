<?php

namespace src\Repositories;

use src\Domain\Entities\Usuario;
use src\Repositories\UsuarioAbstractRepository;
use Ramsey\Uuid\Uuid;
use src\Utils\RelogioTimeZone;
use PDO;

class UsuarioRepository extends UsuarioAbstractRepository
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Salva um usuário no banco de dados
     *
     * @param Usuario $usuario
     * @return void
     */
    public function salvar(Usuario $usuario): void
    {
        $this->executarQuery(function () use ($usuario) {
            try {
                $this->pdo->beginTransaction();

                $agora = RelogioTimeZone::agora()->format('Y-m-d H:i:sP');

                $uuidObj = $usuario->getUuid();
                if (!$uuidObj) {
                    throw new \DomainException('Usuário deve possuir UUID antes de ser persistido');
                }

                $uuid = $uuidObj->toString();
                $existe = $this->buscarPorUuid($uuid) !== null;

                if ($existe) {
                    // UPDATE
                    $sql = "UPDATE {$this->tabela} SET 
                                nome_completo = :nome_completo,
                                email = :email,
                                username = :username,
                                senha_hash = :senha_hash,
                                url_avatar = :url_avatar,
                                url_capa = :url_capa,
                                biografia = :biografia,
                                nivel_acesso = :nivel_acesso,
                                ativo = :ativo,
                                atualizado_em = :atualizado_em
                            WHERE {$this->colunaId} = :uuid";

                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindValue(':nome_completo', $usuario->getNomeCompleto());
                    $stmt->bindValue(':email', $usuario->getEmail());
                    $stmt->bindValue(':username', $usuario->getUsername());
                    $stmt->bindValue(':senha_hash', $usuario->getSenhaHash());
                    $stmt->bindValue(':url_avatar', $usuario->getUrlAvatar());
                    $stmt->bindValue(':url_capa', $usuario->getUrlCapa());
                    $stmt->bindValue(':biografia', $usuario->getBiografia());
                    $stmt->bindValue(':nivel_acesso', $usuario->getNivelAcesso());
                    $stmt->bindValue(':ativo', $usuario->isAtivo() ? 1 : 0, PDO::PARAM_INT);
                    $stmt->bindValue(':uuid', $uuid);
                    $stmt->bindValue(':atualizado_em', $agora);
                    $stmt->execute();
                    if ($stmt->rowCount() === 0) {
                        throw new \RuntimeException('Falha ao atualizar: Nenhuma linha afetada. Verifique se o UUID existe e se os dados realmente mudaram.');
                    }
                } else {
                    // INSERT
                    $sql = "INSERT INTO {$this->tabela} 
                            (uuid, nome_completo, email, username, senha_hash, url_avatar, url_capa, biografia, nivel_acesso, ativo, criado_em) 
                            VALUES (:uuid, :nome_completo, :email, :username, :senha_hash, :url_avatar, :url_capa, :biografia, :nivel_acesso, :ativo, :criado_em)";

                    $stmt = $this->pdo->prepare($sql);
                    $stmt->bindValue(':uuid', $uuid);
                    $stmt->bindValue(':nome_completo', $usuario->getNomeCompleto());
                    $stmt->bindValue(':email', $usuario->getEmail());
                    $stmt->bindValue(':username', $usuario->getUsername());
                    $stmt->bindValue(':senha_hash', $usuario->getSenhaHash());
                    $stmt->bindValue(':url_avatar', $usuario->getUrlAvatar());
                    $stmt->bindValue(':url_capa', $usuario->getUrlCapa());
                    $stmt->bindValue(':biografia', $usuario->getBiografia());
                    $stmt->bindValue(':nivel_acesso', $usuario->getNivelAcesso());
                    $stmt->bindValue(':ativo', $usuario->isAtivo() ? 1 : 0, PDO::PARAM_INT);
                    $stmt->bindValue(':criado_em', $agora);
                    $stmt->execute();
                }

                $this->pdo->commit();

            } catch (\PDOException $e) {
                $this->pdo->rollBack();

                // Violação de UNIQUE / FK / constraint
                if ($e->getCode() === '23000') {
                    throw new \RuntimeException(
                        'Já existe usuário com este email ou username'
                    );
                }

                throw $e;

            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }, 'Erro ao salvar usuário no banco de dados');
    }


    /**
     * Verifica se email já existe
     */
    public function emailExiste(string $email, ?string $excluirUuid = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tabela} WHERE email = :email";
        $params = [':email' => $email];

        if ($excluirUuid) {
            $sql .= " AND {$this->colunaId} != :uuid";
            $params[':uuid'] = $excluirUuid;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Verifica se username já existe
     */
    public function usernameExiste(string $username, ?string $excluirUuid = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tabela} WHERE username = :username";
        $params = [':username' => $username];

        if ($excluirUuid) {
            $sql .= " AND {$this->colunaId} != :uuid";
            $params[':uuid'] = $excluirUuid;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Busca usuários por nome (paginado)
     *
     * @return Usuario[]
     */
    public function buscarPorNomePaginado(
        string $nome,
        int $pagina = 1,
        int $porPagina = 10
    ): array {
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT * FROM {$this->tabela}
                WHERE nome_completo LIKE :nome
                ORDER BY criado_em DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', "%{$nome}%");
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row) => $this->mapearParaEntity($row),
            $resultados
        );
    }

    /**
     * Retorna os últimos usuários criados
     *
     * @return Usuario[]
     */
    public function ultimosCriados(int $quantidade = 10): array
    {
        $sql = "SELECT * FROM {$this->tabela}
                ORDER BY criado_em DESC
                LIMIT :quantidade";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row) => $this->mapearParaEntity($row),
            $resultados
        );
    }
}
