<?php

namespace src\Repositories;


use PDO;
use PDOException;
use src\Domain\Entities\Usuario;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class UsuarioAbstractRepository implements UsuarioRepositoryInterface
{
    /**
     * Nome da tabela
     * @var string
     */
    protected string $tabela = 'usuarios';

    /**
     * Instacia da conexão PDO
     * @var \PDO
     */
    protected PDO $pdo;

    /**
     * Nome da coluna de identificação (Padrão: UUID)
     */
    protected string $colunaId = 'uuid';

    /**
     * Construtor da classe
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Usuario[]
     */
    public function buscarTodos(int $limite = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tabela} LIMIT :limite OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row) => $this->mapearParaEntity($row),
            $resultados
        );
    }

    public function buscarPorUuid(string $uuid): ?Usuario
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$this->colunaId} = :uuid LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $this->mapearParaEntity($row) : null;
    }

    public function buscarPorUsername(string $username): ?Usuario
    {
        return $this->buscarUmPor('username', $username);
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->buscarUmPor('email', $email);
    }

    public function buscarTodosPor(string $coluna, mixed $valor): array
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$coluna} = :valor";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':valor', $valor);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row) => $this->mapearParaEntity($row),
            $resultados
        );
    }

    public function deletar(string $uuid): void
    {
        $sql = "DELETE FROM {$this->tabela} WHERE {$this->colunaId} = :uuid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();
    }

    public function contar(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tabela}";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    protected function buscarUmPor(string $coluna, mixed $valor): ?Usuario
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$coluna} = :valor LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':valor', $valor);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado === false
            ? null
            : $this->mapearParaEntity($resultado);
    }

    /**
     * Converte array do banco em Entity Usuario
     */
    protected function mapearParaEntity(array $dados): Usuario
    {
        return new Usuario(
            Uuid::fromString($dados['uuid']),
            $dados['nomeCompleto'],
            $dados['username'],
            $dados['email'],
            $dados['senhaHash'],
            $dados['nivelAcesso'],
            (bool) $dados['ativo'],
            new DateTimeImmutable($dados['criadoEm']),
            $dados['urlAvatar'] ?? null,
            $dados['urlCapa'] ?? null,
            $dados['biografia'] ?? null,
            $dados['tokenRecuperacaoSenha'] ?? null,
            $dados['tokenVerificacaoEmail'] ?? null,
            isset($dados['atualizadoEm'])
                ? new DateTimeImmutable($dados['atualizadoEm'])
                : null
        );
    }

    abstract public function salvar(Usuario $usuario): void;

}