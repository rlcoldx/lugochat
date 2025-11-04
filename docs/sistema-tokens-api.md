# Sistema de Gerenciamento de Tokens de API

## Visão Geral

O Sistema de Gerenciamento de Tokens permite criar, editar e deletar tokens de acesso à API para cada motel. Cada token é único e permite controlar o acesso aos endpoints da API de integração.

---

## Funcionalidades

### 1. Listar Tokens
- **URL**: `/api/integracao/list`
- **Descrição**: Exibe todos os tokens cadastrados
- **Informações exibidas**:
  - Motel associado
  - Sistema de integração
  - Token (com botão para copiar)
  - Contador de acessos
  - Data de criação
  - Data da última atualização

### 2. Criar Novo Token
- **URL**: `/api/integracao/add`
- **Campos obrigatórios**:
  - Motel
  - Sistema de integração
- **Ação**: Gera automaticamente um token único de 64 caracteres

### 3. Editar Token
- **URL**: `/api/integracao/edit/{id}`
- **Permite editar**:
  - Motel associado (se admin)
  - Sistema de integração
- **Não permite editar**:
  - Token (precisa usar "Gerar Novo Token")

### 4. Gerar Novo Token
- **Ação**: Gera um novo token único e invalida o anterior
- **Alerta**: O token antigo deixa de funcionar imediatamente

### 5. Deletar Token
- **Ação**: Remove o token permanentemente
- **Alerta**: Ação irreversível

---

## Estrutura do Banco de Dados

### Tabela: `api_motel`

```sql
CREATE TABLE `api_motel` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_motel` INT(11) NULL DEFAULT NULL,
	`sistema` VARCHAR(255) NULL DEFAULT NULL,
	`token` VARCHAR(255) NULL DEFAULT NULL,
	`acessos` BIGINT(255) NULL DEFAULT '0',
	`date_create` TIMESTAMP NULL DEFAULT current_timestamp(),
	`date_update` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (`id`),
	INDEX `idx_id_motel` (`id_motel`),
	INDEX `idx_token` (`token`)
);
```

---

## Como Usar

### Para Administradores

1. Acesse o menu **Integração API** no painel admin
2. Clique em **Add Novo Token**
3. Selecione o motel e informe o nome do sistema
4. Clique em **SALVAR TOKEN**
5. O token será gerado automaticamente
6. Copie o token e forneça para o sistema externo

### Para Gestores de Motel

1. Acesse o menu **Integração API**
2. Você verá apenas os tokens do seu motel
3. Pode criar, editar e deletar tokens do seu motel
4. O campo "Motel" será preenchido automaticamente

---

## Segurança

### Boas Práticas

1. **Nunca compartilhe tokens publicamente**
2. **Regenere tokens periodicamente**
3. **Monitore o contador de acessos** para identificar uso anormal
4. **Delete tokens não utilizados**
5. **Use um token diferente para cada sistema externo**

### Formato do Token

- 64 caracteres hexadecimais
- Gerado usando `random_bytes(32)` do PHP
- Exemplo: `a1b2c3d4e5f6...` (64 caracteres)

---

## Integração com API

### Como usar o token nas requisições

#### Exemplo 1: Header Authorization
```bash
curl -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  http://localhost/lugochat/api/integracao/suites
```

#### Exemplo 2: Query Parameter
```bash
curl http://localhost/lugochat/api/integracao/suites?token=SEU_TOKEN_AQUI
```

### Validação do Token

O sistema verifica:
1. Se o token existe na tabela `api_motel`
2. Se o motel associado está ativo
3. Incrementa o contador de acessos

---

## Contador de Acessos

Cada requisição bem-sucedida incrementa o contador `acessos` do token. Use isso para:
- Monitorar uso da API
- Identificar tokens não utilizados
- Detectar uso anormal

---

## Estrutura de Arquivos

```
application/
├── Controllers/
│   └── ApiIntegracao/
│       └── ApiIntegracaoController.php
├── Models/
│   └── ApiIntegracao/
│       └── ApiIntegracao.php
routes/
└── pages/
    └── api-integracao.php
view/
├── pages/
│   └── api-integracao/
│       └── index.twig
├── components/
│   └── api-integracao/
│       └── form.twig
└── assets/
    └── js/
        └── pages/
            └── api-integracao.js
```

---

## Endpoints Disponíveis

### Painel Admin

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/integracao/list` | Listar todos os tokens |
| GET | `/api/integracao/add` | Formulário para criar token |
| GET | `/api/integracao/edit/{id}` | Formulário para editar token |
| POST | `/api/integracao/salvar` | Salvar token (criar/editar) |
| POST | `/api/integracao/deletar/{id}` | Deletar token |
| POST | `/api/integracao/gerar-token/{id}` | Gerar novo token |

### API Pública (com autenticação)

Veja a documentação completa em:
- `docs/documentacao-api.md`
- `docs/api-postman-collection.json`

---

## Troubleshooting

### Token não funciona
1. Verifique se o token foi copiado corretamente (64 caracteres)
2. Confirme que o motel está ativo
3. Verifique se o token não foi deletado ou regenerado

### Erro ao criar token
1. Verifique se o motel foi selecionado
2. Confirme que o campo "Sistema" foi preenchido
3. Verifique permissões de acesso ao banco de dados

### Contador de acessos não incrementa
1. Verifique se a validação do token está implementada nos endpoints
2. Confirme que o método `incrementarAcessos()` está sendo chamado

---

## Exemplos de Uso

### Criar Token via Interface

1. Acesse `/api/integracao/list`
2. Clique em "Add Novo Token"
3. Preencha:
   - **Motel**: Motel Exemplo
   - **Sistema**: Sistema PMS Hoteleiro
4. Clique em "SALVAR TOKEN"
5. Copie o token gerado

### Usar Token na API

```php
// Em seus endpoints de API, adicione validação:
$token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if ($token) {
    $model = new ApiIntegracao();
    $validacao = $model->validarToken($token);
    
    if ($validacao && $validacao['motel_status'] === 'Ativo') {
        // Token válido, processar requisição
        $model->incrementarAcessos($validacao['id']);
        
        // Seu código aqui...
    } else {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido']);
    }
} else {
    http_response_code(401);
    echo json_encode(['erro' => 'Token não fornecido']);
}
```

---

## Próximos Passos

1. Implementar middleware de autenticação por token
2. Adicionar logs de acesso detalhados
3. Criar relatórios de uso por token
4. Implementar rate limiting por token
5. Adicionar webhooks para notificar sobre novos tokens

