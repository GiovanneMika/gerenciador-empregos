# 🚀 API - Gerenciador de Empregos

## 📡 Base URL

```
http://SEU_SERVIDOR:PORTA
```

**Exemplos:**
- Servidor local: `http://localhost:8000`
- Servidor do colega 1: `http://192.168.1.10:8000`
- Servidor do colega 2: `http://meuservidor.com:8000`

⚠️ **IMPORTANTE**: 
- As rotas **NÃO usam prefixo `/api`**
- Acesse diretamente: `http://servidor/users`, `http://servidor/login`, etc.
- Apenas mude a base URL, as rotas são sempre as mesmas!

---

## 🔐 AUTENTICAÇÃO

### Sistema JWT (JSON Web Token)
- **Algoritmo**: HS256
- **Expiração**: 60 minutos (3600 segundos)
- **Header**: `Authorization: Bearer {token}`
- **Formato**: `Bearer eyJ0eXAiOiJKV1QiLCJhbGc...`

### Claims do JWT (quando decodificar)
```json
{
  "sub": 1,              // ID do usuário
  "username": "joao123", // Username único
  "role": "user",        // Sempre "user"
  "exp": 1729353600      // Unix timestamp de expiração
}
```

### Como usar
```javascript
// Exemplo em JavaScript/Fetch
const token = 'eyJ0eXAiOiJKV1QiLCJhbGc...';
fetch('http://servidor:porta/users/1', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

---

## 🛣️ ROTAS

### 1️⃣ Criar Usuário (Registro)

```http
POST /users
Content-Type: application/json
```

**Body:**
```json
{
  "name": "João Silva",
  "username": "joao123",
  "email": "joao@example.com",
  "password": "senha123",
  "phone": "11999999999",
  "experience": "5 anos como desenvolvedor",
  "education": "Ciência da Computação - USP"
}
```

**Validações:**
| Campo | Regra | Observação |
|-------|-------|------------|
| name | Obrigatório, string | Convertido para MAIÚSCULAS automaticamente |
| username | Obrigatório, único, alfanumérico + `_` | Imutável após criação |
| email | Obrigatório, único, email válido | - |
| password | Obrigatório, alfanumérico | **SEM caracteres especiais!** |
| phone | Obrigatório, string | - |
| experience | Obrigatório, string | - |
| education | Obrigatório, string | - |

**Respostas:**

**✅ 201 Created** - Sucesso
```json
{
  "name": "JOÃO SILVA",
  "username": "joao123",
  "email": "joao@example.com",
  "phone": "11999999999",
  "experience": "5 anos como desenvolvedor",
  "education": "Ciência da Computação - USP"
}
```

**❌ 409 Conflict** - Username já existe
```json
{
  "message": "Username already exists"
}
```

**❌ 422 Unprocessable Entity** - Erro de validação
```json
{
  "message": "Validation error",
  "code": "UNPROCESSABLE",
  "details": [
    {
      "field": "email",
      "error": "The email has already been taken."
    }
  ]
}
```

---

### 2️⃣ Login

```http
POST /login
Content-Type: application/json
```

**Body:**
```json
{
  "username": "joao123",
  "password": "senha123"
}
```

**Respostas:**

**✅ 200 OK** - Sucesso
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

**❌ 401 Unauthorized** - Credenciais inválidas
```json
{
  "message": "Invalid credentials"
}
```

---

### 3️⃣ Logout

```http
POST /logout
Authorization: Bearer {token}
```

**Respostas:**

**✅ 200 OK** - Sucesso
```json
{
  "message": "OK"
}
```

**❌ 401 Unauthorized** - Token inválido/expirado
```json
{
  "message": "Invalid Token"
}
```

⚠️ **ATENÇÃO - Comportamento do Logout:**
- O token é invalidado no servidor pelo JWTAuth
- O token pode continuar tecnicamente válido até expirar (60 min)
- **Sempre remova o token do armazenamento local após logout!**
- Se o servidor não usar blacklist, o token pode funcionar até expirar

---

### 4️⃣ Ver Usuário

```http
GET /users/{id}
Authorization: Bearer {token}
```

**Parâmetros:**
- `{id}` - ID do usuário (número inteiro)

**Respostas:**

**✅ 200 OK** - Sucesso
```json
{
  "name": "JOÃO SILVA",
  "username": "joao123",
  "email": "joao@example.com",
  "phone": "11999999999",
  "experience": "5 anos como desenvolvedor",
  "education": "Ciência da Computação - USP"
}
```

**❌ 401 Unauthorized** - Token inválido/expirado
```json
{
  "message": "Invalid Token"
}
```

**❌ 403 Forbidden** - Tentou acessar perfil de outro usuário
```json
{
  "message": "Forbidden"
}
```

**❌ 404 Not Found** - Usuário não encontrado
```json
{
  "message": "User not found"
}
```

🔒 **Autorização**: Usuário só pode ver **próprio perfil**!

---

### 5️⃣ Atualizar Usuário

```http
PATCH /users/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Parâmetros:**
- `{id}` - ID do usuário (número inteiro)

**Body:** (todos campos opcionais)
```json
{
  "name": "João Pedro Silva",
  "email": "joaopedro@example.com",
  "password": "novaSenha456",
  "phone": "11988888888",
  "experience": "6 anos como desenvolvedor sênior",
  "education": "Mestrado em Engenharia de Software"
}
```

⚠️ **IMPORTANTE:**
- ❌ **NÃO pode alterar `username`** (campo imutável)
- ✅ Todos os campos são opcionais (envie apenas o que deseja alterar)
- ✅ Validações iguais ao cadastro

**Respostas:**

**✅ 200 OK** - Sucesso
```json
{
  "message": "User updated successfully"
}
```

**❌ 401 Unauthorized** - Token inválido/expirado
```json
{
  "message": "Invalid Token"
}
```

**❌ 403 Forbidden** - Tentou alterar perfil de outro usuário
```json
{
  "message": "Forbidden"
}
```

**❌ 404 Not Found** - Usuário não encontrado
```json
{
  "message": "User not found"
}
```

**❌ 422 Unprocessable Entity** - Erro de validação
```json
{
  "message": "Validation error",
  "code": "UNPROCESSABLE",
  "details": [
    {
      "field": "email",
      "error": "The email has already been taken."
    }
  ]
}
```

🔒 **Autorização**: Usuário só pode atualizar **próprio perfil**!

---

### 6️⃣ Deletar Usuário

```http
DELETE /users/{id}
Authorization: Bearer {token}
```

**Parâmetros:**
- `{id}` - ID do usuário (número inteiro)

**Respostas:**

**✅ 200 OK** - Sucesso
```json
{
  "message": "User deleted successfully"
}
```

**❌ 401 Unauthorized** - Token inválido/expirado
```json
{
  "message": "Invalid Token"
}
```

**❌ 403 Forbidden** - Tentou deletar perfil de outro usuário
```json
{
  "message": "Forbidden"
}
```

**❌ 404 Not Found** - Usuário não encontrado
```json
{
  "message": "User not found"
}
```

🔒 **Autorização**: Usuário só pode deletar **próprio perfil**!

---

## 📊 CÓDIGOS HTTP

| Código | Status | Significado |
|--------|--------|-------------|
| 200 | OK | Operação realizada com sucesso |
| 201 | Created | Recurso criado com sucesso |
| 401 | Unauthorized | Token inválido, expirado ou ausente |
| 403 | Forbidden | Sem permissão (tentou acessar/modificar outro usuário) |
| 404 | Not Found | Recurso não encontrado |
| 409 | Conflict | Conflito de dados (username já existe) |
| 422 | Unprocessable Entity | Erro de validação nos dados enviados |
| 500 | Server Error | Erro interno do servidor |

---

## 🎯 REGRAS DE NEGÓCIO

### Autorização
- ✅ Cada usuário **só pode ver/editar/deletar próprio perfil**
- ✅ ID do usuário é extraído do token JWT (campo `sub`)
- ❌ Tentativa de acessar outro usuário resulta em **403 Forbidden**

### Validações de Campos

**Username:**
- Alfanumérico + underline (`a-zA-Z0-9_`)
- Único no sistema
- **Imutável** após criação (não pode ser alterado)

**Password:**
- Alfanumérico **apenas** (`a-zA-Z0-9`)
- ❌ **NÃO aceita** underline ou caracteres especiais
- Hash seguro com bcrypt no backend

**Email:**
- Formato válido de email
- Único no sistema
- Pode ser alterado (se não duplicar)

**Name:**
- String qualquer
- **Convertido para MAIÚSCULAS** automaticamente

---

## 💡 EXEMPLO DE FLUXO COMPLETO

```javascript
const BASE_URL = 'http://servidor:porta'; // Mude apenas isso!

// 1️⃣ CRIAR USUÁRIO
const registerResponse = await fetch(`${BASE_URL}/users`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: "Maria Santos",
    username: "maria123",
    email: "maria@email.com",
    password: "senha789",
    phone: "11987654321",
    experience: "3 anos",
    education: "Engenharia"
  })
});
// → 201 Created

// 2️⃣ FAZER LOGIN
const loginResponse = await fetch(`${BASE_URL}/login`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: "maria123",
    password: "senha789"
  })
});
const { token, expires_in } = await loginResponse.json();
// → 200 OK com token

// 3️⃣ VER PERFIL (salve o token!)
const profileResponse = await fetch(`${BASE_URL}/users/1`, {
  headers: { 
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
const profile = await profileResponse.json();
// → 200 OK com dados do usuário

// 4️⃣ ATUALIZAR PERFIL
const updateResponse = await fetch(`${BASE_URL}/users/1`, {
  method: 'PATCH',
  headers: { 
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    phone: "11999999999"
  })
});
// → 200 OK

// 5️⃣ LOGOUT
const logoutResponse = await fetch(`${BASE_URL}/logout`, {
  method: 'POST',
  headers: { 
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
// → 200 OK

// ⚠️ REMOVA O TOKEN DO ARMAZENAMENTO!
localStorage.removeItem('token');
```

---

## 🔧 CORS

A API está configurada para aceitar requisições de **qualquer origem**:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: *
Access-Control-Allow-Headers: *
```

✅ Não precisa se preocupar com CORS durante o desenvolvimento!

---

## 🧪 TESTANDO COM cURL

```bash
# Definir base URL
BASE_URL="http://localhost:8000"

# 1. Criar usuário
curl -X POST $BASE_URL/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "username": "test123",
    "email": "test@test.com",
    "password": "test123",
    "phone": "123456789",
    "experience": "teste",
    "education": "teste"
  }'

# 2. Login
TOKEN=$(curl -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test123","password":"test123"}' \
  | jq -r '.token')

# 3. Ver usuário
curl $BASE_URL/users/1 \
  -H "Authorization: Bearer $TOKEN"

# 4. Atualizar usuário
curl -X PATCH $BASE_URL/users/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"phone":"987654321"}'

# 5. Logout
curl -X POST $BASE_URL/logout \
  -H "Authorization: Bearer $TOKEN"

# 6. Deletar usuário
curl -X DELETE $BASE_URL/users/1 \
  -H "Authorization: Bearer $TOKEN"
```

---

## ⚠️ DIFERENÇAS IMPORTANTES

Diferenças deste projeto em relação a APIs REST comuns:

1. ❌ **SEM prefixo `/api`** nas rotas
2. ✅ **Name convertido para MAIÚSCULAS** automaticamente
3. ✅ **Password SEM caracteres especiais** (só alfanumérico)
4. ✅ **Username COM underline** (alfanumérico + `_`)
5. ✅ **Username imutável** (não pode ser alterado)
6. ✅ **Claims customizados no JWT** (`sub`, `username`, `role`, `exp`)
7. ✅ **Autorização rigorosa** (só acessa próprio perfil)

---

## 🚨 TRATAMENTO DE ERROS

### Padrão de Respostas de Erro

**Erro Simples:**
```json
{
  "message": "Descrição do erro"
}
```

**Erro de Validação (422):**
```json
{
  "message": "Validation error",
  "code": "UNPROCESSABLE",
  "details": [
    {
      "field": "nome_do_campo",
      "error": "mensagem específica"
    }
  ]
}
```

### Como Tratar no Frontend

```javascript
try {
  const response = await fetch(url, options);
  const data = await response.json();
  
  if (!response.ok) {
    // Erro HTTP (4xx, 5xx)
    if (response.status === 422) {
      // Erro de validação
      console.log('Erros:', data.details);
      data.details.forEach(error => {
        console.log(`${error.field}: ${error.error}`);
      });
    } else if (response.status === 401) {
      // Token inválido - redirecionar para login
      console.log('Token inválido, faça login novamente');
      redirectToLogin();
    } else if (response.status === 403) {
      // Sem permissão
      console.log('Você não tem permissão para isso');
    } else {
      // Outro erro
      console.log('Erro:', data.message);
    }
  } else {
    // Sucesso!
    console.log('Dados:', data);
  }
} catch (error) {
  // Erro de rede ou parse
  console.error('Erro na requisição:', error);
}
```

---

## 📝 CHECKLIST DE INTEGRAÇÃO

### Para conectar seu frontend:

- [ ] Configurar base URL do servidor (localhost ou servidor do colega)
- [ ] Implementar armazenamento de token (localStorage/sessionStorage)
- [ ] Adicionar header `Authorization: Bearer {token}` em rotas protegidas
- [ ] Adicionar header `Content-Type: application/json` em todos os POSTs/PATCHs
- [ ] Tratar erro 401 (redirecionar para login)
- [ ] Tratar erro 403 (mostrar mensagem de permissão negada)
- [ ] Tratar erro 422 (mostrar erros de validação por campo)
- [ ] Implementar logout (POST /logout + remover token do storage)
- [ ] Lembrar: username NÃO pode ser alterado!
- [ ] Lembrar: password deve ser alfanumérico apenas!
- [ ] Lembrar: name será convertido para MAIÚSCULAS!

---

## 🔗 LINKS ÚTEIS

- **JWT.io**: https://jwt.io (para decodificar tokens)
- **Postman**: https://www.postman.com (para testar APIs)
- **Insomnia**: https://insomnia.rest (alternativa ao Postman)

---

## 📞 SUPORTE

Dúvidas sobre a API? Entre em contato com o time de backend!

**Versão**: 1.0.0  
**Última atualização**: 19 de outubro de 2025
