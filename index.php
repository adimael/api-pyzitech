<?php

use src\Utils\RelogioTimeZone;

require_once __DIR__ . '/bootstrap.php';

//echo "Current Timezone: " . RelogioTimeZone::obterTimeZone()->getName() . PHP_EOL;
//echo "Current Date and Time: " . RelogioTimeZone::agora()->format('d-m-Y H:i:s') . PHP_EOL;

use src\Domain\Entities\Usuario;
use src\Database\PostgreSQL\Conexao as PostgresConexao;
use src\Database\Exceptions\DatabaseConnectionException;
use src\Http\Response\Response;
use src\Repositories\UsuarioRepository;
use src\Services\UsuarioService;
use src\Http\Controllers\UsuarioController;
use src\Http\Controllers\IndexController;

// Conexão apenas com PostgreSQL, conforme .env
$pdo = PostgresConexao::conectar();
$usuarioRepo = new UsuarioRepository($pdo);
$usuarioService = new UsuarioService($usuarioRepo);
$controller = new UsuarioController($usuarioService);
$indexController = new IndexController();

/* Teste via controller */
$response = $indexController->index();
$response->Enviar();


/* Criação de usuário via controller */
/*
$data = [
    'nome_completo' => 'Maria Silva',
    'username'      => 'maria.silva',
    'email'         => 'maria.silva@example.com',
    'senha'         => '12345678Maria',
    'url_avatar'    => null,
    'url_capa'      => null,
    'biografia'     => 'QA Tester',
    'nivel_acesso'  => 'usuario'
];
$response = $controller->criar($data);
$response->Enviar();
*/

/* Listagem de usuários via controller */
/*
$response = $controller->listar(1, 10);
$response->Enviar();
*/

/* Busca de usuário por UUID */
/*
$uuid = '7f1c2f3a-50bb-4476-8c59-30dba83ca480';
$response = $controller->buscar($uuid);
$response->Enviar();
*/

/* Desativação de usuário */
/*
$uuid = 'c5bca4b0-131f-406a-a572-a3f3adfcbdaa';
$response = $controller->desativar($uuid);
$response->Enviar();
*/

/* Ativação de usuário */
/*
$uuid = 'c5bca4b0-131f-406a-a572-a3f3adfcbdaa';
$response = $controller->ativar($uuid);
$response->Enviar();
*/

/* Deleção de usuário */
/*
$uuid = 'c5bca4b0-131f-406a-a572-a3f3adfcbdaa';
$response = $controller->deletar($uuid);
$response->Enviar();
*/

/* Fim dos testes via controller */

/* Testes diretos no service/repository */

//var_dump($usuarioService->desativar('429e7733-dd62-409a-a360-5f5a40d2c36c'));

/* Listagem de usuários */
/*
$usuarios = $usuarioService->listar(1, 10);
if (empty($usuarios)) {
    echo "Nenhum usuário encontrado." . PHP_EOL;
} else {
    echo "Lista de usuários:" . PHP_EOL;
    foreach ($usuarios as $usuario) {
        echo "- UUID: " . $usuario->getUuid()->toString() . PHP_EOL;
        echo "  Nome: " . $usuario->getNomeCompleto() . PHP_EOL;
        echo "  Username: " . $usuario->getUsername() . PHP_EOL;
        echo "  Email: " . $usuario->getEmail() . PHP_EOL;
        echo "  Ativo: " . ($usuario->isAtivo() ? 'Sim' : 'Não') . PHP_EOL;
        echo "  Criado em: " . $usuario->getCriadoEm()->format('d/m/Y H:i:s') . PHP_EOL;
        echo str_repeat('-', 30) . PHP_EOL;
    }
}
*/

/* Atualização de usuário existente */

// UUID real do banco
/*
$uuid = '7f1c2f3a-50bb-4476-8c59-30dba83ca480';

// Busca
$usuario = $usuarioService->buscarPorUuid($uuid);

if (!$usuario) {
    echo "Usuário não encontrado";
    exit;
}

// Altera dados
$usuario->setNomeCompleto('Adimael S.');
$usuario->setEmail('adimaelbr@gmail.com');

// Salva (faz UPDATE)
$usuarioService->atualizar($usuario);

echo "Usuário atualizado com sucesso!";
*/

/* Criação de novo usuário */

/*
try {
    $usuario = Usuario::registrar(
            nomeCompleto: 'Tales Oliveira',
            username: 'pedro.silva',
            email: 'pedro.silva@gmail.com',
            senha: '12345678Ad',
            urlAvatar: null,
            urlCapa: null,
            biografia: 'Desenvolvedor JAVA',
            nivelAcesso: 'usuario'
    );
} catch (Exception $e) {
    echo "Erro ao registrar usuário: " . $e->getMessage() . PHP_EOL;
}


try {
    $usuarioService->criar($usuario);

    echo "Usuário criado com sucesso!" . PHP_EOL;
    echo "UUID: " . $usuario->getUuid()->toString() . PHP_EOL;
    echo "Nome: " . $usuario->getNomeCompleto() . PHP_EOL;
    echo "Email: " . $usuario->getEmail() . PHP_EOL;
} catch (\Throwable $e) {

    echo "Erro: " . $e->getMessage() . PHP_EOL;

    if ($e->getPrevious()) {
        echo "Motivo real: " . $e->getPrevious()->getMessage();
    }
}
*/


/*
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
*/

/* Ativar/Desativar usuário */
/*
$uuid = '429e7733-dd62-409a-a360-5f5a40d2c36c';
try {
    $usuarioService->desativar($uuid);
    echo "Usuário desativado com sucesso!" . PHP_EOL;
} catch (Exception $e) {
    echo "Erro ao desativar usuário: " . $e->getMessage() . PHP_EOL;
}
*/

/* Excluir usuário */
/*
$uuid = '429e7733-dd62-409a-a360-5f5a40d2c36c';
try {
    $usuarioService->deletar($uuid);
    echo "Usuário excluído com sucesso!" . PHP_EOL;
} catch (Exception $e) {
    echo "Erro ao excluir usuário: " . $e->getMessage() . PHP_EOL;
}
*/

/* Fim dos testes diretos no service/repository */