# API Pyzitech

API REST desenvolvida em PHP seguindo boas práticas, arquitetura limpa e padrões de projeto.

## 📋 Descrição

API Pyzitech é uma API REST completa para gerenciamento de usuários, construída com PHP 8+ utilizando princípios de Clean Architecture, DDD (Domain-Driven Design) e padrões de projeto como Repository Pattern, Service Layer e Dependency Injection.

## 🚀 Como Começar

### 1. Clonar o Repositório

```bash
git clone https://github.com/adimael/api-pyzitech.git
cd api-pyzitech
```

### 2. Instalar Dependências

```bash
composer install
```

### 3. Configurar Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto copiando o exemplo:

```bash
cp .env.example .env
```

Configure as variáveis necessárias no arquivo `.env`:

```env
# Configuração da aplicação
APP_NAME=API Pyzitech
APP_VERSION=1.0.0
APP_ENV=development
APP_DEBUG=true
APP_PORT=8000
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Bahia

# Configuração do banco de dados (PostgreSQL)
DB_CONEXAO=postgresql
DB_HOST=localhost
DB_PORT=5432
DB_NOME=pyzitech_api
DB_USUARIO=seu_usuario
DB_SENHA=sua_senha

# Configuração do JWT
JWT_SECRET=sua_chave_secreta_jwt_aqui
JWT_ISSUER=http://localhost:8000
JWT_AUDIENCE=http://localhost:8000
JWT_EXPIRATION_TIME=3600

# Outras configurações...
```

### 4. Rodar o Projeto

#### Usando PHP Built-in Server:

```bash
php -S localhost:8000 -t .
```

#### Ou usando Docker (se disponível):

```bash
# Compose ou comando docker run
```

Acesse: `http://localhost:8000`

## 🏗️ Estrutura do Projeto

```
src/
├── Configs/                 # Configurações da aplicação
│   └── EnvConfig.php
├── Database/               # Camada de acesso a dados
│   ├── Exceptions/         # Exceções específicas do banco
│   ├── Mysql/             # Conexão MySQL (não utilizado)
│   └── PostgreSQL/        # Conexão PostgreSQL
│       └── Conexao.php
├── Domain/                 # Camada de domínio (DDD)
│   ├── Entities/          # Entidades de domínio
│   │   └── Usuario.php
│   └── Exceptions/        # Exceções de domínio
├── Exceptions/             # Exceções da aplicação
├── Http/                   # Camada HTTP
│   ├── Controllers/       # Controladores
│   ├── Middlewares/       # Middlewares
│   ├── Request/           # Objeto Request
│   └── Response/          # Objeto Response
├── Repositories/           # Repositórios (Repository Pattern)
├── Routes/                 # Sistema de rotas
├── Services/               # Camada de serviço (Service Layer)
└── Utils/                  # Utilitários
```

## 🏛️ Principais Classes

### Core Classes

- **`src\Routes\Router`** - Roteador principal da aplicação
- **`src\Routes\Route`** - Classe auxiliar para definição de rotas
- **`src\Http\Request\Request`** - Representa uma requisição HTTP
- **`src\Http\Response\Response`** - Representa uma resposta HTTP
- **`src\Http\Controllers\UsuarioController`** - Controlador de usuários
- **`src\Http\Middlewares\AuthMiddleware`** - Middleware de autenticação
- **`src\Services\AuthService`** - Serviço de autenticação JWT
- **`src\Domain\Entities\Usuario`** - Entidade de domínio Usuário
- **`src\Repositories\UsuarioRepository`** - Repositório de usuários
- **`src\Database\PostgreSQL\Conexao`** - Conexão com PostgreSQL

## 🔌 Endpoints

### 🔓 Endpoints Públicos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/` | Status da aplicação |

### 🔐 Endpoints Privados (Requer Autenticação)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/usuarios` | Lista usuários (paginado) |
| `GET` | `/api/usuario/{uuid}` | Busca usuário por UUID |
| `POST` | `/api/criar/usuario` | Cria novo usuário |
| `PUT` | `/api/usuario/{uuid}` | Atualiza usuário |
| `DELETE` | `/api/usuario/{uuid}` | Deleta usuário |
| `PATCH` | `/api/usuario/{uuid}/desativar` | Desativa usuário |
| `PATCH` | `/api/usuario/{uuid}/ativar` | Ativa usuário |

### Headers Requeridos para Endpoints Privados

```
Authorization: Bearer {seu_token_jwt}
```

Ou para acesso de super admin:
```
Authorization: Bearer {JWT_SECRET_do_.env}
```

### Exemplos de Uso

#### Criar Usuário
```bash
curl -X POST http://localhost:8000/api/criar/usuario \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer seu_token_jwt" \
  -d '{
    "nome_completo": "João Silva",
    "username": "joaosilva",
    "email": "joao@email.com",
    "senha": "Senha@123",
    "url_avatar": "https://exemplo.com/avatar.jpg",
    "biografia": "Desenvolvedor PHP"
  }'
```

#### Listar Usuários
```bash
curl -X GET "http://localhost:8000/api/usuarios?page=1&per_page=10" \
  -H "Authorization: Bearer seu_token_jwt"
```

#### Atualizar Usuário
```bash
curl -X PUT http://localhost:8000/api/usuario/{uuid} \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer seu_token_jwt" \
  -d '{
    "nome_completo": "João Silva Atualizado",
    "biografia": "Desenvolvedor Senior PHP"
  }'
```

## 🛠️ Como Criar Novos Endpoints

### 1. Criar Controller

Crie um novo controller em `src/Http/Controllers/`:

```php
<?php

namespace src\Http\Controllers;

use src\Http\Response\Response;

class MeuController
{
    public function meuMetodo($request): Response
    {
        // Lógica do endpoint
        return Response::json([
            'message' => 'Endpoint funcionando!'
        ]);
    }
}
```

### 2. Registrar Rota

Adicione a rota em `src/Routes/web.php`:

```php
// Para endpoint público
Route::get('/api/meu-endpoint', [MeuController::class, 'meuMetodo']);

// Para endpoint privado (requer autenticação)
Route::get('/api/meu-endpoint', [MeuController::class, 'meuMetodo'], AuthMiddleware::class);
```

### 3. Tipos de Rotas Disponíveis

```php
// GET - Recuperar dados
Route::get('/api/recurso', [Controller::class, 'metodo']);

// POST - Criar recurso
Route::post('/api/recurso', [Controller::class, 'metodo']);

// PUT - Atualizar recurso inteiro
Route::put('/api/recurso/{id}', [Controller::class, 'metodo']);

// DELETE - Remover recurso
Route::delete('/api/recurso/{id}', [Controller::class, 'metodo']);

// PATCH - Atualização parcial
Route::patch('/api/recurso/{id}', [Controller::class, 'metodo']);
```

## 🔐 Como Definir Endpoints Públicos vs Privados

### Endpoint Público (Sem Autenticação)
```php
// Em src/Routes/web.php
Route::get('/api/publico', [MeuController::class, 'metodoPublico']);
```

### Endpoint Privado (Com Autenticação)
```php
// Em src/Routes/web.php
Route::get('/api/privado', [MeuController::class, 'metodoPrivado'], AuthMiddleware::class);
```

### Diferença Principal

- **Público**: Qualquer pessoa pode acessar sem token
- **Privado**: Requer header `Authorization: Bearer {token}`

## 🛡️ Sistema de Autenticação

### JWT Token Structure
O sistema utiliza JWT (JSON Web Tokens) para autenticação:

```
Header.Payload.Signature
```

### Super Admin Access
Para acesso de super admin, utilize o valor de `JWT_SECRET` do `.env` como token:

```
Authorization: Bearer {valor_de_JWT_SECRET}
```

### Validação de Token
O middleware `AuthMiddleware` verifica:
1. Presença do token
2. Validade da assinatura
3. Expiração do token
4. Existência do usuário

## ⚙️ Configurações Importantes

### Banco de Dados
- Suporte a PostgreSQL (principal) e MySQL
- Conexão gerenciada via Singleton
- Tratamento de exceções específico por tipo de erro

### Timezone
Configurável via `APP_TIMEZONE` no `.env`

### Debug Mode
Ative/desative modo debug com `APP_DEBUG=true/false`

## 🧪 Testes

Para executar testes (quando disponíveis):
```bash
# Comandos para testes futuros
```

## 📦 Dependências

- `vlucas/phpdotenv:^5.6` - Gerenciamento de variáveis de ambiente
- `ramsey/uuid:^4.9` - Geração de UUIDs

## 🤝 Contribuição

1. Fork o projeto
2. Crie sua feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👨‍💻 Autor

**Adimael** - adimaelbr@gmail.com

## 🆘 Suporte

Para suporte, envie um email para adimaelbr@gmail.com ou abra uma issue no GitHub.