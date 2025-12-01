# 📋 Gerenciador de Empregos - API REST

Sistema de gerenciamento de usuários e empregos desenvolvido com Laravel 12, utilizando autenticação JWT e banco de dados SQLite.

**Projeto para a disciplina de Tecnologias Cliente-Servidor**

## 📝 Sobre o Projeto

API RESTful desenvolvida para gerenciar usuários, empresas e vagas de emprego. O sistema implementa autenticação segura via JWT (JSON Web Token) suportando dois tipos de entidades (usuários e empresas), validação robusta de dados e segue boas práticas de desenvolvimento de APIs.

### Principais Funcionalidades

- ✅ Cadastro de usuários e empresas com validação de dados
- 🔐 Autenticação JWT com tokens de 60 minutos (multi-modelo: usuários e empresas)
- 👤 Consulta, edição e exclusão de perfis com autorização baseada em proprietário
- 🏢 Gerenciamento completo de empresas (cadastro, edição, deleção)
- 💼 Criação e gerenciamento de vagas de emprego pelas empresas
- 📋 Aplicação para vagas com status tracking (pendente, aceita, rejeitada)
- 📊 Armazenamento de experiência profissional e formação acadêmica (usuários)
- 🌐 CORS configurado para acesso externo
- 🔒 Blacklist de tokens para logout seguro

## 🛠️ Tecnologias Utilizadas

- **PHP 8.2+** - Linguagem de programação
- **Laravel 12** - Framework PHP
- **SQLite** - Banco de dados (portátil)
- **JWT Auth (tymon/jwt-auth)** - Autenticação via tokens
- **Composer** - Gerenciador de dependências PHP
- **Docker** (opcional) - Containerização

## 📋 Requisitos do Sistema

### Opção 1: Docker (Recomendado)
- Docker 20.10+
- Docker Compose 1.29+

### Opção 2: Manual
- PHP 8.2 ou superior
- Composer 2.0+
- SQLite3
- Extensões PHP: `pdo_sqlite`, `mbstring`, `openssl`, `bcmath`

## 🚀 Como Executar o Projeto

### 📦 Opção 1: Usando Docker (Mais Simples - Recomendado)

**Requisitos:**
- Docker 20.10+
- Docker Compose 1.29+

**Passo a passo:**

```powershell
# 1. Abra PowerShell ou CMD na pasta do projeto
cd C:\caminho\para\gerenciador-empregos

# 2. Copie o arquivo de ambiente
Copy-Item .env.example .env

# 3. Crie o arquivo SQLite (banco de dados)
New-Item -ItemType File -Path database\database.sqlite -Force

# 4. Construa e inicie os containers
docker-compose up -d --build

# 5. Aguarde o build finalizar (pode levar 2-3 minutos na primeira vez)

# 6. Verifique se tudo está rodando
docker-compose ps

# 7. Acesse a API
http://localhost:8000
```

**Se ver a página "Welcome" significa que tudo está funcionando! ✅**

#### Comandos Úteis do Docker

```powershell
# Ver logs em tempo real
docker-compose logs -f app

# Parar os containers (sem deletar)
docker-compose stop

# Parar e remover containers
docker-compose down

# Reiniciar os containers
docker-compose restart

# Executar comando artisan
docker-compose exec app php artisan migrate

# Executar comando artisan com bash
docker-compose exec app bash
```

---

### 💻 Opção 2: Execução Manual (Sem Docker)

**Requisitos:**
- PHP 8.2 ou superior
- Composer 2.0+
- SQLite3
- Extensões PHP: `pdo_sqlite`, `mbstring`, `openssl`, `bcmath`

**Verificar requisitos (Windows PowerShell):**
```powershell
php -v          # Verifica versão do PHP
composer -v     # Verifica versão do Composer
php -m | grep pdo_sqlite   # Verifica extensão SQLite
```

**Passo a passo:**

```powershell
# 1. Abra PowerShell ou CMD na pasta do projeto
cd C:\caminho\para\gerenciador-empregos

# 2. Copie o arquivo de ambiente
Copy-Item .env.example .env

# 3. Edite o .env se necessário (geralmente não é)
# Abra com editor e verifique DB_DATABASE
# Se no Windows: DB_DATABASE=C:\Users\SeuUsuario\...\gerenciador-empregos\database\database.sqlite

# 4. Crie o arquivo do banco de dados
New-Item -ItemType File -Path database\database.sqlite -Force

# 5. Instale as dependências
composer install

# 6. Gere a chave da aplicação
php artisan key:generate

# 7. Execute as migrations (cria as tabelas)
php artisan migrate

# 8. Inicie o servidor de desenvolvimento
php artisan serve --host=0.0.0.0 --port=8000

# 9. Em outro terminal PowerShell, você pode testar:
curl -X GET http://localhost:8000/
```

**Se receber HTML como resposta significa que tudo está funcionando! ✅**

---

## 📡 Endpoints da API

### Autenticação

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| POST | `/users` | Criar novo usuário | ❌ Não |
| POST | `/companies` | Criar nova empresa | ❌ Não |
| POST | `/login` | Autenticar (usuário ou empresa) | ❌ Não |
| POST | `/logout` | Deslogar | ✅ Sim |

### Usuários

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| GET | `/users/{id}` | Buscar usuário por ID | ✅ Sim |
| PATCH | `/users/{id}` | Atualizar usuário | ✅ Sim (apenas próprio) |
| DELETE | `/users/{id}` | Deletar usuário | ✅ Sim (apenas próprio) |

### Empresas

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| GET | `/companies/{id}` | Buscar empresa por ID | ✅ Sim |
| PATCH | `/companies/{id}` | Atualizar empresa | ✅ Sim (apenas própria) |
| DELETE | `/companies/{id}` | Deletar empresa | ✅ Sim (apenas própria) |

### Vagas de Emprego

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| POST | `/jobs` | Criar vaga de emprego | ✅ Sim (empresa) |
| GET | `/jobs/{id}` | Buscar vaga por ID | ✅ Sim |
| PATCH | `/jobs/{id}` | Atualizar vaga | ✅ Sim (empresa proprietária) |
| DELETE | `/jobs/{id}` | Deletar vaga | ✅ Sim (empresa proprietária) |
| GET | `/companies/{id}/jobs` | Listar vagas de uma empresa | ✅ Sim |
| POST | `/jobs/search` | Buscar vagas com filtros | ✅ Sim |

### Candidaturas

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| POST | `/job-applications` | Candidatar a vaga | ✅ Sim (usuário) |
| GET | `/job-applications/{id}` | Buscar candidatura | ✅ Sim |
| PATCH | `/job-applications/{id}` | Atualizar status candidatura | ✅ Sim (empresa) |

### Exemplos de Uso

#### 1. Criar Usuário
```bash
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "JOAO SILVA",
    "username": "joao_silva",
    "password": "senha123",
    "email": "joao@email.com",
    "phone": "11999999999",
    "experience": "Desenvolvedor PHP com 3 anos de experiência",
    "education": "Bacharelado em Ciência da Computação"
  }'
```

**Resposta (201 Created):**
```json
{
  "message": "Created"
}
```

#### 2. Criar Empresa
```bash
curl -X POST http://localhost:8000/companies \
  -H "Content-Type: application/json" \
  -d '{
    "name": "TECH SOLUTIONS LTDA",
    "business": "Desenvolvimento de Software",
    "username": "tech_solutions",
    "password": "senha123",
    "street": "Rua das Flores",
    "number": "123",
    "city": "São Paulo",
    "state": "SP",
    "phone": "1133334444",
    "email": "contact@techsolutions.com"
  }'
```

**Resposta (201 Created):**
```json
{
  "message": "Created"
}
```

#### 3. Login (Usuário ou Empresa)
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "joao_silva",
    "password": "senha123"
  }'
```

**Resposta (200 OK):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 3600
}
```

#### 4. Buscar Usuário (com token)
```bash
curl -X GET http://localhost:8000/users/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

**Resposta (200 OK):**
```json
{
  "name": "JOAO SILVA",
  "username": "joao_silva",
  "email": "joao@email.com",
  "phone": "11999999999",
  "experience": "Desenvolvedor PHP com 3 anos de experiência",
  "education": "Bacharelado em Ciência da Computação"
}
```

#### 5. Criar Vaga de Emprego (como Empresa)
```bash
curl -X POST http://localhost:8000/jobs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "title": "Desenvolvedor PHP Senior",
    "area": "Tecnologia da Informação",
    "description": "Buscamos um desenvolvedor PHP com experiência em Laravel para integrar nossa equipe",
    "state": "SP",
    "city": "São Paulo",
    "salary": 7500.00
  }'
```

**Resposta (201 Created):**
```json
{
  "message": "Created"
}
```

**⚠️ Nota:** O email de contato é automaticamente preenchido com o email da empresa cadastrada.

#### 6. Buscar Vaga
```bash
curl -X GET http://localhost:8000/jobs/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "title": "Desenvolvedor PHP Senior",
  "area": "Tecnologia da Informação",
  "description": "Buscamos um desenvolvedor PHP com experiência em Laravel...",
  "state": "SP",
  "city": "São Paulo",
  "salary": 7500.00,
  "contact": "contact@techsolutions.com",
  "company": {
    "id": 1,
    "name": "TECH SOLUTIONS LTDA"
  },
  "created_at": "2025-12-01T10:30:00Z"
}
```

#### 7. Candidatar a Vaga (como Usuário)
```bash
curl -X POST http://localhost:8000/job-applications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "job_vacancy_id": 1
  }'
```

**Resposta (201 Created):**
```json
{
  "message": "Created"
}
```

#### 8. Logout
```bash
curl -X POST http://localhost:8000/logout \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

**Resposta (200 OK):**
```json
{
  "message": "OK"
}
```

## 📚 Documentação Completa

Para documentação detalhada da API, incluindo:
- Todos os códigos de status HTTP
- Formatos de erro
- Validações de campos
- Regras de negócio
- Exemplos em JavaScript/Fetch

**Consulte:** `API_DOCUMENTATION.md`

## 🔧 Estrutura do Projeto

```
gerenciador-empregos/
├── app/
│   ├── Http/
│   │   ├── Controllers/API/  # Controllers da API
│   │   └── Resources/        # Resources para formatação de resposta
│   ├── Models/               # Models do Eloquent
│   └── Policies/             # Políticas de autorização
├── bootstrap/
│   └── app.php              # Configuração de exceções
├── config/                  # Arquivos de configuração
├── database/
│   ├── migrations/          # Migrations do banco
│   └── database.sqlite      # Banco de dados SQLite
├── routes/
│   └── api.php              # Rotas da API
├── storage/                 # Arquivos gerados
├── .env                     # Variáveis de ambiente
├── .env.example             # Exemplo de configuração
├── composer.json            # Dependências PHP
├── Dockerfile               # Configuração Docker
├── docker-compose.yml       # Orquestração Docker
├── API_DOCUMENTATION.md     # Documentação completa da API
└── README.md               # Este arquivo
```

## 🔒 Segurança

- ✅ Senhas criptografadas com bcrypt
- ✅ JWT com expiração de 60 minutos
- ✅ Validação rigorosa de entrada de dados
- ✅ Autorização baseada em políticas (Policy)
- ✅ Senha nunca retornada nas respostas da API
- ✅ Proteção contra injeção SQL (Eloquent ORM)

## ⚙️ Configurações Importantes

### Banco de Dados
- **Tipo:** SQLite (arquivo único, portátil)
- **Localização:** `database/database.sqlite`
- **Vantagem:** Não precisa instalar servidor de banco de dados

### Autenticação JWT
- **Algoritmo:** HS256
- **Expiração:** 60 minutos
- **Claims customizados:** `sub`, `username`, `role`, `exp`

### CORS
- **Configuração:** Aceita todas as origens (`*`)
- **Arquivo:** `config/cors.php`

## 🐛 Solução de Problemas

### ❌ Erro: "database is locked"

**Causa:** Múltiplas instâncias do servidor acessando o banco simultaneamente.

**Solução Docker:**
```powershell
docker-compose restart
```

**Solução Manual:**
```powershell
# Pressione Ctrl+C no terminal onde o servidor está rodando
# Aguarde alguns segundos
php artisan serve --host=0.0.0.0 --port=8000
```

---

### ❌ Erro: "Porta 8000 já está em uso"

**Solução:**
```powershell
# Use outra porta
php artisan serve --port=8001

# Ou no Docker, edite docker-compose.yml:
# Mude "8000:8000" para "8001:8000"
docker-compose down
docker-compose up -d --build
```

---

### ❌ Erro: "Class 'JWT' not found" ou dependências não encontradas

**Solução:**
```powershell
composer dump-autoload

# Se não resolver:
composer install
```

---

### ❌ Erro: "Permission denied" no banco de dados

**Windows:** Clique direito no arquivo `database/database.sqlite` → Propriedades → Desmarque "Somente leitura"

**Linux/Mac:**
```bash
chmod 664 database/database.sqlite
chmod 755 database/
```

---

### ❌ Erro: "Failed to connect to Docker daemon"

**Causa:** Docker não está rodando.

**Solução:**
- Windows: Abra Docker Desktop
- Linux: `sudo systemctl start docker`

---

### ❌ Erro: "Invalid Token" em todas as requisições

**Causa:** Token expirou ou foi invalidado (logout).

**Solução:** Faça login novamente para obter um novo token.

---

### ❌ Erro: "Forbidden" (403) ao criar vaga

**Causa:** Você está usando token de usuário, não de empresa.

**Solução:** Faça login como empresa para criar vagas.

```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "tech_solutions",
    "password": "senha123"
  }'
```

---

### ❌ Erro: "Validation error" (422)

**Significado:** Um ou mais campos foram rejeitados.

**Verifique:**
- Todos os campos obrigatórios foram enviados?
- Os valores estão no formato correto?
- Limites de caracteres respeitados?

**Exemplo de resposta:**
```json
{
  "message": "Validation error",
  "code": "UNPROCESSABLE",
  "details": [
    {
      "field": "email",
      "error": "The email must be a valid email address."
    }
  ]
}
```

---

### ❌ Erro: "Connection refused" em http://localhost:8000

**Verificação:**
```powershell
# Docker está rodando?
docker-compose ps

# Se não estiver, inicie:
docker-compose up -d --build

# Manual está rodando?
# Verifique se o terminal mostra "Server running..."
```

---

### 🔧 Como Acessar os Logs

**Docker:**
```powershell
docker-compose logs -f app
```

**Manual:**
```powershell
# Os logs aparecem no terminal em tempo real
# Se quiser ver logs salvos:
cat storage\logs\laravel.log
```

---

### 🔧 Reset Completo (Limpar Tudo)

**Docker:**
```powershell
docker-compose down -v
docker-compose up -d --build
```

**Manual:**
```powershell
# Delete e recrie o banco
Remove-Item database\database.sqlite -Force
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

## 📝 Validações e Regras de Negócio

### Usuários

#### Campos Obrigatórios
- **name**: 4-150 caracteres (convertido para MAIÚSCULO automaticamente)
- **username**: 3-20 caracteres, alfanumérico com underscore, único
- **password**: 3-20 caracteres, apenas letras e números

#### Campos Opcionais
- **email**: Formato de email válido
- **phone**: 10-14 dígitos numéricos
- **experience**: 10-600 caracteres
- **education**: 10-600 caracteres

#### Regras Especiais
- ❌ Username **não pode** ser alterado após criação
- ✅ Apenas o próprio usuário pode editar/deletar seu perfil
- ✅ Nome é sempre armazenado em MAIÚSCULO
- ✅ Senha nunca é retornada nas consultas

### Empresas

#### Campos Obrigatórios
- **name**: 4-150 caracteres (único, não pode repetir)
- **business**: 4-150 caracteres (ramo de negócio)
- **username**: 3-20 caracteres, alfanumérico, único
- **password**: 3-20 caracteres, apenas letras e números
- **street**: 3-150 caracteres
- **number**: 1-8 dígitos numéricos
- **city**: 3-150 caracteres
- **state**: 2 caracteres (siglas dos estados brasileiros)
- **phone**: 10-14 dígitos numéricos
- **email**: Formato de email válido, único

#### Regras Especiais
- ✅ Apenas a própria empresa pode editar/deletar seu perfil
- ✅ Ao atualizar, verifica se novo nome já não existe em outra empresa
- ✅ Senha nunca é retornada nas consultas

### Vagas de Emprego

#### Campos Obrigatórios
- **title**: 3-150 caracteres
- **area**: Um dos 24 tipos de área (ex: "Tecnologia da Informação", "Marketing", "Vendas")
- **description**: 10-5000 caracteres
- **state**: 2 caracteres (sigla do estado)
- **city**: 2-150 caracteres

#### Campos Opcionais
- **salary**: Valor numérico maior que 0

#### Campos Automáticos
- **contact**: Preenchido automaticamente com email da empresa
- **company_id**: Preenchido automaticamente com ID da empresa autenticada

#### Regras Especiais
- ✅ Apenas empresas podem criar/editar vagas
- ✅ Apenas a empresa proprietária pode editar/deletar sua vaga
- ✅ Email de contato é sempre o email atual da empresa
- ✅ Não há necessidade de enviar o campo `contact` na requisição

### Candidaturas

#### Campos Obrigatórios
- **job_vacancy_id**: ID da vaga a qual candidatar

#### Status Possíveis
- **pending**: Candidatura enviada, aguardando resposta
- **accepted**: Candidatura aceita pela empresa
- **rejected**: Candidatura rejeitada pela empresa

#### Regras Especiais
- ✅ Apenas usuários podem candidatar
- ✅ Empresas podem aceitar ou rejeitar candidaturas
- ✅ Um usuário pode candidatar apenas uma vez por vaga

## 📦 Bibliotecas Incluídas

Todas as bibliotecas necessárias estão incluídas via Composer:

- `laravel/framework` (^12.0) - Framework Laravel
- `tymon/jwt-auth` (^2.2) - Autenticação JWT
- `laravel/sanctum` (^4.0) - Autenticação adicional
- E outras dependências (veja `composer.json`)

## 🎯 Protocolo de API

Este projeto segue um protocolo específico de API com:
- ❌ Sem prefixo `/api` (rotas diretas na raiz)
- ✅ Suporte a autenticação multi-modelo (usuários e empresas no mesmo guard)
- ✅ Formato específico de erros de validação
- ✅ Códigos HTTP padronizados
- ✅ Claims JWT customizados com `role` (user ou company)
- ✅ Token único com 60 minutos de validade
- ✅ Blacklist para logout seguro

### Resposta de Sucesso
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

### Resposta de Erro (401 - Token Inválido)
```json
{
  "message": "Invalid Token"
}
```

### Resposta de Erro (422 - Validação)
```json
{
  "message": "Validation error",
  "code": "UNPROCESSABLE",
  "details": [
    {
      "field": "email",
      "error": "The email field is required."
    },
    {
      "field": "password",
      "error": "The password must be at least 3 characters."
    }
  ]
}
```

**Veja detalhes completos em:** `API_DOCUMENTATION.md`

## 👨‍💻 Testando a API

### 1️⃣ Cadastrar um Usuário

```bash
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "JOAO SILVA",
    "username": "joao_silva",
    "password": "senha123",
    "email": "joao@email.com",
    "phone": "11999999999",
    "experience": "Desenvolvedor PHP com 3 anos",
    "education": "Bacharelado em Ciência da Computação"
  }'
```

**Esperado:** Status 201 com mensagem "Created"

### 2️⃣ Cadastrar uma Empresa

```bash
curl -X POST http://localhost:8000/companies \
  -H "Content-Type: application/json" \
  -d '{
    "name": "TECH SOLUTIONS LTDA",
    "business": "Desenvolvimento de Software",
    "username": "tech_solutions",
    "password": "senha123",
    "street": "Rua das Flores",
    "number": "123",
    "city": "São Paulo",
    "state": "SP",
    "phone": "1133334444",
    "email": "contact@techsolutions.com"
  }'
```

**Esperado:** Status 201 com mensagem "Created"

### 3️⃣ Fazer Login como Usuário

```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "joao_silva",
    "password": "senha123"
  }'
```

**Resposta esperada:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

**Copie o token para usar nos próximos passos!**

### 4️⃣ Criar uma Vaga de Emprego (como Empresa)

Primeiro, faça login como empresa:

```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "tech_solutions",
    "password": "senha123"
  }'
```

Então crie a vaga:

```bash
curl -X POST http://localhost:8000/jobs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer EMPRESA_TOKEN" \
  -d '{
    "title": "Desenvolvedor PHP Senior",
    "area": "Tecnologia da Informação",
    "description": "Buscamos um desenvolvedor PHP com experiência em Laravel",
    "state": "SP",
    "city": "São Paulo",
    "salary": 7500.00
  }'
```

**Esperado:** Status 201 com mensagem "Created"

**⚠️ Nota:** Não é necessário enviar `contact` - é preenchido automaticamente!

### 5️⃣ Candidatar a Vaga (como Usuário)

Use o token do usuário do passo 3:

```bash
curl -X POST http://localhost:8000/job-applications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer USUARIO_TOKEN" \
  -d '{
    "job_vacancy_id": 1
  }'
```

**Esperado:** Status 201 com mensagem "Created"

### 6️⃣ Testando com Postman/Insomnia

1. Configure a base URL: `http://localhost:8000`
2. Para requisições que precisam autenticação:
   - Aba **Headers**
   - Adicione: `Authorization` = `Bearer {seu_token}`
   - Ou use a aba **Auth** → Bearer Token

### 7️⃣ Códigos de Erro Esperados

| Status | Significado | Exemplo |
|--------|-------------|---------|
| 200 | OK | Login bem-sucedido |
| 201 | Criado | Usuário/empresa criada |
| 401 | Não autorizado | Token inválido ou ausente |
| 403 | Proibido | Tentando editar perfil de outro |
| 404 | Não encontrado | Recurso não existe |
| 409 | Conflito | Username/email já existe |
| 422 | Validação | Dados inválidos |

## 📄 Arquivos Importantes

- **`README.md`** - Este arquivo com instruções gerais
- **`API_DOCUMENTATION.md`** - Documentação completa da API
- **`SETUP_INSTRUCTIONS.txt`** - Instruções detalhadas de setup
- **`.env.example`** - Exemplo de configuração de ambiente
- **`composer.json`** - Lista de dependências PHP

## 🎓 Informações Acadêmicas

Este projeto foi desenvolvido como trabalho acadêmico seguindo especificações de protocolo para interoperabilidade entre sistemas de diferentes alunos.

### Características do Projeto
- ✅ API RESTful completa com suporte a múltiplos tipos de entidades
- ✅ Autenticação JWT multi-modelo (usuários e empresas)
- ✅ Sistema de vagas de emprego e candidaturas
- ✅ Autorização com Policies
- ✅ Validação robusta de dados
- ✅ Banco de dados portátil (SQLite)
- ✅ Dockerizado para fácil execução
- ✅ Documentação completa e exemplos práticos

## 📞 Suporte

Para dúvidas sobre execução do projeto:
1. Consulte `SETUP_INSTRUCTIONS.txt` para troubleshooting
2. Verifique `API_DOCUMENTATION.md` para detalhes da API
3. Verifique os logs: `storage/logs/laravel.log`

## 📋 Checklist de Entrega

- ✅ Código fonte completo
- ✅ Bibliotecas incluídas (vendor/ via composer)
- ✅ Arquivo `.env.example` configurado
- ✅ Migrations do banco de dados
- ✅ Documentação completa (README.md)
- ✅ Instruções de execução detalhadas
- ✅ Dockerfile e docker-compose.yml
- ✅ Documentação da API completa

---

## 🚀 Início Rápido (Resumo)

### Docker (3 comandos - Windows PowerShell):
```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Path database\database.sqlite -Force
docker-compose up -d --build
```

### Docker (3 comandos - Linux/Mac):
```bash
cp .env.example .env
touch database/database.sqlite
docker-compose up -d --build
```

### Manual (6 comandos - Windows PowerShell):
```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Path database\database.sqlite -Force
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

### Manual (6 comandos - Linux/Mac):
```bash
cp .env.example .env
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

**Acesse:** http://localhost:8000

---

## 🔐 Autenticação Multi-Modelo

O sistema suporta autenticação tanto para **usuários** quanto para **empresas** com um único endpoint `/login`:

### Fluxo de Autenticação

1. **Usuário faz login com suas credenciais**
   - Sistema verifica nas tabelas `users` e `companies`
   - Se encontrado em `users`: retorna token com `role: "user"`
   - Se encontrado em `companies`: retorna token com `role: "company"`

2. **Token JWT contém**
   - `sub`: ID do usuário/empresa
   - `username`: Username
   - `role`: "user" ou "company"
   - `exp`: Expiração em 60 minutos

3. **Autorização por Policies**
   - Controllers verificam se `$user instanceof Company` ou `User`
   - Apenas o proprietário pode editar/deletar seu recurso
   - Empresas só criam/editam vagas
   - Usuários só se candidatam a vagas

### Como Testar a Autenticação

```bash
# 1. Login como Usuário
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"username":"usuario_teste","password":"senha123"}'

# Resposta inclui: token com role: "user"

# 2. Login como Empresa
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"username":"empresa_teste","password":"senha123"}'

# Resposta inclui: token com role: "company"

# 3. Usar token para acessar recurso
curl -X GET http://localhost:8000/users/1 \
  -H "Authorization: Bearer TOKEN_DO_PASSO_1"

# 4. Tentar acessar rota de empresa sem ser empresa
curl -X POST http://localhost:8000/jobs \
  -H "Authorization: Bearer TOKEN_DO_PASSO_1" \
  -H "Content-Type: application/json" \
  -d '...'

# Retorna: 403 Forbidden (apenas empresas podem criar vagas)
```

**Desenvolvido com Laravel 12 para a disciplina de Tecnologias Cliente-Servidor** 🚀
