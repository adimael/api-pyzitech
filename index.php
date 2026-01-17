<?php

use src\Utils\RelogioTimeZone;

require_once __DIR__ . '/bootstrap.php';

echo "Current Timezone: " . RelogioTimeZone::obterTimeZone()->getName() . PHP_EOL;
echo "Current Date and Time: " . RelogioTimeZone::agora()->format('d-m-Y H:i:s') . PHP_EOL;

use src\Domain\Entities\Usuario;

try {
    $usuario = Usuario::registrar(
        nomeCompleto: 'Adimael Santos da Silva',
        username: 'adimael',
        email: 'adimaelbr@gmail.com',
        senha: 'SenhaSegura123',
        urlAvatar: null,
        urlCapa: null,
        biografia: 'Desenvolvedor PHP',
        nivelAcesso: 'usuario'
        );
        echo "UUID: " . $usuario->getUuid()->toString() . PHP_EOL;
        echo "Usuário registrado com sucesso: " . $usuario->getUsername() . PHP_EOL;
        echo "Email: " . $usuario->getEmail() . PHP_EOL;
        echo "Nível de Acesso: " . $usuario->getNivelAcesso() . PHP_EOL;
        echo "Ativo: " . ($usuario->isAtivo() ? 'Sim' : 'Não') . PHP_EOL;
        echo "Nome Completo: " . $usuario->getNomeCompleto() . PHP_EOL;
        echo "Biografia: " . $usuario->getBiografia() . PHP_EOL;
        echo "URL do Avatar: " . ($usuario->getUrlAvatar() ?? 'Não informado') . PHP_EOL;
        echo "URL da Capa: " . ($usuario->getUrlCapa() ?? 'Não informado') . PHP_EOL;
        echo "Senha Hash: " . $usuario->getSenhaHash() . PHP_EOL;
        echo "Atualizado em: " . ($usuario->getAtualizadoEm() ? $usuario->getAtualizadoEm()->format('d-m-Y H:i:s') : 'Nunca atualizado') . PHP_EOL;
        echo "Criado em: " . $usuario->getCriadoEm()->format('d-m-Y H:i:s') . PHP_EOL;
    } catch (Exception $e) {
        echo "Erro ao registrar usuário: " . $e->getMessage() . PHP_EOL;
}