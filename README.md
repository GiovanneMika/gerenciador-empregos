# 📋 Gerenciador de Empregos - API REST

Sistema de gerenciamento de usuários e empregos desenvolvido com Laravel 12, utilizando autenticação JWT e banco de dados SQLite.

**Projeto para a disciplina de Tecnologias Cliente-Servidor**

## 📝 Sobre o Projeto

API RESTful desenvolvida para gerenciar usuários e suas informações profissionais, incluindo experiência e formação acadêmica. O sistema implementa autenticação segura via JWT (JSON Web Token) e segue boas práticas de desenvolvimento de APIs.

### Principais Funcionalidades

- ✅ Cadastro de usuários com validação de dados
- 🔐 Autenticação JWT com tokens de 60 minutos
- 👤 Consulta, edição e exclusão de perfis
- 🔒 Autorização baseada em proprietário (usuário só edita seu próprio perfil)
- 📊 Armazenamento de experiência profissional e formação acadêmica
- 🌐 CORS configurado para acesso externo

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

### 📦 Opção 1: Usando Docker (Mais Simples)

```powershell
# 1. Extraia o arquivo ZIP do projeto
# 2. Navegue até a pasta do projeto
cd gerenciador-empregos

# 3. Configure o arquivo de ambiente
Copy-Item .env.example .env

# 4. Crie o arquivo do banco de dados
New-Item -ItemType File -Path database\database.sqlite -Force

# 5. Inicie o Docker (isso pode levar alguns minutos na primeira vez)
docker-compose up -d --build

# 6. Aguarde o build finalizar e acesse:
# http://localhost:8000
```

**Pronto!** A API estará rodando em `http://localhost:8000`

#### Comandos Úteis do Docker

```powershell
# Ver logs em tempo real
docker-compose logs -f app

# Parar os containers
docker-compose down

# Reiniciar os containers
docker-compose restart

# Executar comandos artisan
docker-compose exec app php artisan route:list
```

---

### 💻 Opção 2: Execução Manual (Sem Docker)

```powershell
# 1. Extraia o arquivo ZIP do projeto
# 2. Navegue até a pasta do projeto
cd gerenciador-empregos

# 3. Configure o arquivo de ambiente
Copy-Item .env.example .env

# 4. Edite o arquivo .env e ajuste o caminho do banco de dados
# Exemplo Windows: DB_DATABASE=C:\Users\SeuUsuario\caminho\gerenciador-empregos\database\database.sqlite

# 5. Crie o arquivo do banco de dados
New-Item -ItemType File -Path database\database.sqlite -Force

# 6. Instale as dependências
composer install

# 7. Gere a chave da aplicação
php artisan key:generate

# 8. Execute as migrations (cria as tabelas no banco)
php artisan migrate

# 9. Inicie o servidor
php artisan serve --host=0.0.0.0 --port=8000

# 10. Acesse: http://localhost:8000
```

**Pronto!** A API estará rodando em `http://localhost:8000`

---

## 📡 Endpoints da API

### Autenticação

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| POST | `/users` | Criar novo usuário | ❌ Não |
| POST | `/login` | Autenticar usuário | ❌ Não |
| POST | `/logout` | Deslogar usuário | ✅ Sim |

### Usuários

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| GET | `/users/{id}` | Buscar usuário | ✅ Sim |
| PATCH | `/users/{id}` | Atualizar usuário | ✅ Sim (apenas próprio perfil) |
| DELETE | `/users/{id}` | Deletar usuário | ✅ Sim (apenas próprio perfil) |

### Exemplos de Uso

#### 1. Criar Usuário
```bash
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "JOAO SILVA",
    "username": "joao_silva",
    "password": "senha123",
    "role": "user",
    "email": "joao@email.com",
    "phone": "11999999999",
    "experience": "Desenvolvedor PHP com 3 anos de experiência",
    "education": "Bacharelado em Ciência da Computação"
  }'
```

#### 2. Login
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "joao_silva",
    "password": "senha123"
  }'
```

**Resposta:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

#### 3. Buscar Usuário (com token)
```bash
curl -X GET http://localhost:8000/users/1 \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

**Resposta:**
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

### Erro: "database is locked"
```powershell
# Reinicie o servidor
# Docker:
docker-compose restart

# Manual:
# Pressione Ctrl+C e execute novamente:
php artisan serve --host=0.0.0.0 --port=8000
```

### Erro: "Porta 8000 já está em uso"
```powershell
# Use outra porta:
php artisan serve --port=8001

# Ou no Docker, edite docker-compose.yml:
# Mude "8000:8000" para "8001:8000"
```

### Erro: "Class 'JWT' not found"
```powershell
composer dump-autoload
```

### Erro: "Permission denied" no banco de dados
```powershell
# Linux/Mac:
chmod 664 database/database.sqlite

# Windows:
# Clique direito no arquivo > Propriedades > Desmarque "Somente leitura"
```

## 📝 Validações e Regras de Negócio

### Campos Obrigatórios
- **name**: 4-150 caracteres (convertido para MAIÚSCULO automaticamente)
- **username**: 3-20 caracteres, alfanumérico com underscore, único
- **password**: 3-20 caracteres, apenas letras e números (sem underscore)
- **role**: "admin" ou "user"

### Campos Opcionais
- **email**: Formato de email válido
- **phone**: 10-14 dígitos numéricos
- **experience**: 10-600 caracteres
- **education**: 10-600 caracteres

### Regras Especiais
- ❌ Username **não pode** ser alterado após criação
- ✅ Apenas o próprio usuário pode editar/deletar seu perfil
- ✅ Nome é sempre armazenado em MAIÚSCULO
- ✅ Senha nunca é retornada nas consultas

## 📦 Bibliotecas Incluídas

Todas as bibliotecas necessárias estão incluídas via Composer:

- `laravel/framework` (^12.0) - Framework Laravel
- `tymon/jwt-auth` (^2.2) - Autenticação JWT
- `laravel/sanctum` (^4.0) - Autenticação adicional
- E outras dependências (veja `composer.json`)

## 🎯 Protocolo de API

Este projeto segue um protocolo específico de API com:
- ❌ Sem prefixo `/api` (rotas diretas na raiz)
- ✅ Formato específico de erros de validação
- ✅ Códigos HTTP padronizados
- ✅ Claims JWT customizados

**Veja detalhes completos em:** `API_DOCUMENTATION.md`

## 👨‍💻 Testando a API

### Método 1: cURL (Terminal)
Use os exemplos de cURL fornecidos na seção "Exemplos de Uso"

### Método 2: Postman/Insomnia
1. Importe a coleção de requisições
2. Configure a base URL: `http://localhost:8000`
3. Após login, adicione o token no header: `Authorization: Bearer {token}`

### Método 3: Navegador (apenas GET)
```
http://localhost:8000/users/1
```
(Requer extensão para adicionar header de autenticação)

## 📄 Arquivos Importantes

- **`README.md`** - Este arquivo com instruções gerais
- **`API_DOCUMENTATION.md`** - Documentação completa da API
- **`SETUP_INSTRUCTIONS.txt`** - Instruções detalhadas de setup
- **`.env.example`** - Exemplo de configuração de ambiente
- **`composer.json`** - Lista de dependências PHP

## 🎓 Informações Acadêmicas

Este projeto foi desenvolvido como trabalho acadêmico seguindo especificações de protocolo para interoperabilidade entre sistemas de diferentes alunos.

### Características do Projeto
- ✅ API RESTful completa
- ✅ Autenticação JWT
- ✅ Autorização com Policies
- ✅ Validação robusta de dados
- ✅ Banco de dados portátil (SQLite)
- ✅ Dockerizado para fácil execução
- ✅ Documentação completa

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

### Docker (3 comandos):
```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Path database\database.sqlite -Force
docker-compose up -d --build
```

### Manual (6 comandos):
```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Path database\database.sqlite -Force
composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

**Acesse:** http://localhost:8000

---

**Desenvolvido com Laravel 12 para a disciplina de Tecnologias Cliente-Servidor** 🚀
