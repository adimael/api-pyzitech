<?php

namespace src\Domain\Entities;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use src\Utils\RelogioTimeZone;
use src\Domain\Exceptions;
use src\Domain\Exceptions\InvalidEmailException;
use src\Domain\Exceptions\InvalidPasswordException;
use src\Domain\Exceptions\InvalidUsernameException;


final class Usuario
{
    private UuidInterface $uuid;
    private string $nomeCompleto;
    private string $username;
    private string $email;
    private string $senhaHash;
    private ?string $urlAvatar;
    private ?string $urlCapa;
    private ?string $biografia;
    private string $nivelAcesso;
    private ?string $tokenRecuperacaoSenha;
    private ?string $tokenVerificacaoEmail;
    private bool $ativo;
    private DateTimeImmutable $criadoEm;
    private ?DateTimeImmutable $atualizadoEm;

    private const NIVEIS_VALIDOS = ['usuario', 'admin', 'moderador'];

    private function __construct(
        UuidInterface $uuid,
        string $nomeCompleto,
        string $username,
        string $email,
        string $senhaHash,
        string $nivelAcesso,
        bool $ativo,
        DateTimeImmutable $criadoEm,
        ?string $urlAvatar = null,
        ?string $urlCapa = null,
        ?string $biografia = null,
        ?string $tokenRecuperacaoSenha = null,
        ?string $tokenVerificacaoEmail = null,
        ?DateTimeImmutable $atualizadoEm = null
    )
    {
        $this->uuid = $uuid;
        $this->nomeCompleto = $nomeCompleto;
        $this->username = $username;
        $this->email = $email;
        $this->senhaHash = $senhaHash;
        $this->nivelAcesso = $nivelAcesso;
        $this->ativo = $ativo;
        $this->criadoEm = $criadoEm;
        $this->urlAvatar = $urlAvatar;
        $this->urlCapa = $urlCapa;
        $this->biografia = $biografia;
        $this->tokenRecuperacaoSenha = $tokenRecuperacaoSenha;
        $this->tokenVerificacaoEmail = $tokenVerificacaoEmail;
        $this->atualizadoEm = $atualizadoEm;
    }

    private static function validarNivelAcesso(string $nivel): void
    {
        if (!in_array($nivel, self::NIVEIS_VALIDOS, true)) {
            throw new \InvalidArgumentException('Nível de acesso inválido.');
        }
    }

    public static function registrar(
        string $nomeCompleto,
        string $username,
        string $email,
        string $senha,
        ?string $urlAvatar = null,
        ?string $urlCapa = null,
        ?string $biografia = null,
        string $nivelAcesso = 'usuario'
    ): self
    {
        self::validarUsername($username);
        self::validarEmail($email);
        self::validarSenha($senha);
        self::validarNivelAcesso($nivelAcesso);

        return new self(
            Uuid::uuid4(),
            $nomeCompleto,
            $username,
            $email,
            password_hash($senha, PASSWORD_DEFAULT),
            $nivelAcesso,
            true,
            RelogioTimeZone::agora(),
            $urlAvatar,
            $urlCapa,
            $biografia,
            null, // tokenRecuperacaoSenha
            null, // tokenVerificacaoEmail
            null  // atualizadoEm
        );
    }

    public static function reconstituir(
            UuidInterface $uuid,
            string $nomeCompleto,
            string $username,
            string $email,
            string $senhaHash,
            string $nivelAcesso,
            bool $ativo,
            DateTimeImmutable $criadoEm,
            ?string $urlAvatar = null,
            ?string $urlCapa = null,
            ?string $biografia = null,
            ?string $tokenRecuperacaoSenha = null,
            ?string $tokenVerificacaoEmail = null,
            ?DateTimeImmutable $atualizadoEm = null
        ): self {
            return new self(
                $uuid,
                $nomeCompleto,
                $username,
                $email,
                $senhaHash,
                $nivelAcesso,
                $ativo,
                $criadoEm,
                $urlAvatar,
                $urlCapa,
                $biografia,
                $tokenRecuperacaoSenha,
                $tokenVerificacaoEmail,
                $atualizadoEm
            );
    }

    private static function validarEmail(string $email): void
    {
        if (trim($email) === '') {
            throw new InvalidEmailException('E-mail não informado.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Formato de e-mail inválido.');
        }
    }

    private static function validarUsername(string $username): void
    {
        if (trim($username) === '') {
            throw new InvalidUsernameException('Username não informado.');
        }
        // Não pode iniciar com caractere especial
        if (preg_match('/^[._]/', $username)) {
            throw new InvalidUsernameException('Username não pode iniciar com caractere especial (ponto ou underline).');
        }
        // Deve ter ao menos 3 caracteres
        if (strlen($username) < 3) {
            throw new InvalidUsernameException('Username deve ter ao menos 3 caracteres.');
        }
        // Só pode conter letras, números, ponto e underline
        if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
            throw new InvalidUsernameException('Username só pode conter letras, números, ponto ou underline.');
        }
        // Só pode conter UM caractere especial (ponto ou underline)
        $matches = preg_match_all('/[._]/', $username);
        if ($matches !== false && $matches > 1) {
            throw new InvalidUsernameException(
                'Username pode conter apenas um caractere especial (ponto ou underline).'
            );
        }
    }

    private static function validarSenha(string $senha): void
    {
        if (trim($senha) === '') {
            throw new InvalidPasswordException('Senha não informada.');
        }
        if (strlen($senha) < 8) {
            throw new InvalidPasswordException('Senha muito curta. Mínimo de 8 caracteres.');
        }
        // Exemplo: pelo menos uma letra maiúscula, uma minúscula e um número
        $temMaiuscula = preg_match('/[A-Z]/', $senha);
        $temMinuscula = preg_match('/[a-z]/', $senha);

        if (!$temMaiuscula) {
            throw new InvalidPasswordException('Senha deve conter ao menos uma letra maiúscula.');
        }
        if (!$temMinuscula) {
            throw new InvalidPasswordException('Senha deve conter ao menos uma letra minúscula.');
        }
        if (!preg_match('/[0-9]/', $senha)) {
            throw new InvalidPasswordException('Senha deve conter ao menos um número.');
        }
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getNomeCompleto(): string
    {
        return $this->nomeCompleto;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function getUrlAvatar(): ?string
    {
        return $this->urlAvatar;
    }

    public function getUrlCapa(): ?string
    {
        return $this->urlCapa;
    }

    public function getBiografia(): ?string
    {
        return $this->biografia;
    }

    public function getNivelAcesso(): string
    {
        return $this->nivelAcesso;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    public function getAtualizadoEm(): ?DateTimeImmutable
    {
        return $this->atualizadoEm;
    }

    public function setNomeCompleto(string $nomeCompleto): void
    {
        $this->nomeCompleto = $nomeCompleto;
    }

    public function setUsername(string $username): void
    {
        self::validarUsername($username);
        $this->username = $username;
    }

    public function setEmail(string $email): void
    {
        self::validarEmail($email);
        $this->email = $email;
    }

    public function verificarSenha(string $senhaPlana): bool
    {
        return password_verify($senhaPlana, $this->senhaHash);
    }

    public function alterarSenha(string $senhaPlana): void
    {
        self::validarSenha($senhaPlana);
        $this->senhaHash = password_hash($senhaPlana, PASSWORD_DEFAULT);
    }

    public function setUrlAvatar(?string $urlAvatar): void
    {
        $this->urlAvatar = $urlAvatar;
    }

    public function setUrlCapa(?string $urlCapa): void
    {
        $this->urlCapa = $urlCapa;
    }

    public function setBiografia(?string $biografia): void
    {
        $this->biografia = $biografia;
    }

    public function ativar(): void
    {
        $this->ativo = true;
        $this->atualizadoEm = RelogioTimeZone::agora();
    }

    public function desativar(): void
    {
        $this->ativo = false;
        $this->atualizadoEm = RelogioTimeZone::agora();
    }

    public function promoverPara(string $nivelAcesso): void
    {
        if (!in_array($nivelAcesso, self::NIVEIS_VALIDOS, true)) {
            throw new \InvalidArgumentException('Nível de acesso inválido.');
        }

        $this->nivelAcesso = $nivelAcesso;
        $this->atualizadoEm = RelogioTimeZone::agora();
    }

    public function gerarTokenRecuperacaoSenha(string $token): void
    {
        $this->tokenRecuperacaoSenha = $token;
        $this->atualizadoEm = RelogioTimeZone::agora();
    }

    public function gerarTokenVerificacaoEmail(string $token): void
    {
        $this->tokenVerificacaoEmail = $token;
        $this->atualizadoEm = RelogioTimeZone::agora();
    }

    public function setCriadoEm(DateTimeImmutable $criadoEm): void
    {
        $this->criadoEm = $criadoEm;
    }

    public function setAtualizadoEm(?DateTimeImmutable $atualizadoEm): void
    {
        $this->atualizadoEm = $atualizadoEm;
    }

    public function __toString(): string
    {
        $uuid = $this->getUuid()->toString();
        $nome = $this->getNomeCompleto();
        $username = $this->getUsername();
        $email = $this->getEmail();
        $nivelAcesso = $this->getNivelAcesso();
        $ativo = $this->isAtivo() ? 'Sim' : 'Não';
        $criadoEm = $this->getCriadoEm()->format('d-m-Y H:i:s');
        return "Usuário [UUID: {$uuid}, Nome: {$nome}, Username: {$username}, Email: {$email}, Nível de Acesso: {$nivelAcesso}, Ativo: {$ativo}, Criado Em: {$criadoEm}]";
    }

}

