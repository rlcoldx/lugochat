# Documentação do Sistema Rubens

## Visão Geral

O Sistema Rubens é uma integração de reservas para o sistema LugoChat. Este sistema permite a gestão de reservas, disponibilidade de suítes e integração com sistemas externos através de uma API REST.

---

## Estrutura do Sistema

### Arquivos Principais

- **Controller**: `application/Controllers/Rubens/RubensController.php`
- **Model**: `application/Models/Rubens/Rubens.php`
- **Rotas**: `routes/API/rubens.php`

---

## Controller: RubensController

**Localização**: `application/Controllers/Rubens/RubensController.php`

### Responsabilidades

O `RubensController` é responsável por:
- Receber requisições HTTP da API
- Validar parâmetros de entrada
- Chamar métodos do Model para processar dados
- Retornar respostas JSON formatadas

### Métodos do Controller

#### 1. `suites()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/suites`
- **Descrição**: Retorna todas as suítes de todos os motéis que têm integração Rubens ativa
- **Retorno**: JSON com estrutura hierárquica de motéis e suas suítes
- **Uso**: Consulta geral de suítes disponíveis

#### 2. `disponibilidade($params)`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/suite/disponibilidade`
- **Parâmetros Query**:
  - `motel` (obrigatório): ID do motel
  - `suite` (obrigatório): ID da suíte
  - `qtde` (obrigatório): Quantidade de disponibilidade a ser atualizada
- **Descrição**: Atualiza a disponibilidade de uma suíte específica em um motel
- **Validações**: Verifica se o motel existe e está ativo com integração Rubens
- **Retorno**: `'ok'` em caso de sucesso ou mensagem de erro

#### 3. `qtde_disp()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/suite/qtde_disp`
- **Parâmetros Query**:
  - `motel` (obrigatório): ID do motel
  - `suite` (opcional): ID da suíte específica
- **Descrição**: Retorna a quantidade de disponibilidade de suítes de um motel
- **Retorno**: JSON com informações de disponibilidade

#### 4. `CriarReservaTeste()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/criar/teste`
- **Parâmetros Query**:
  - `motel` (obrigatório): ID do motel
  - `suite` (obrigatório): ID da suíte
- **Descrição**: Cria uma pré-reserva de teste com dados fictícios para testar a integração
- **Validações**: Verifica se a suíte está disponível antes de criar
- **Retorno**: JSON com `reserva_id` da reserva criada

#### 5. `verReserva()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/ver`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Retorna informações completas de uma reserva, incluindo dados de pagamento
- **Retorno**: JSON com todos os dados da reserva e pagamento

#### 6. `reservaPaga()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/reserva_paga`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Simula o pagamento aprovado de uma reserva (para testes)
- **Ações**: Atualiza status do pagamento para 'approved' e fase para 2
- **Retorno**: JSON com `result: 'atualizado'`

#### 7. `cancelarReserva()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/cancelar`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Simula o cancelamento de uma reserva (para testes)
- **Ações**: Atualiza status do pagamento para 'cancelled' e marca como cancelada
- **Retorno**: JSON com `result: 'cancelada'`

#### 8. `naoPagarReserva()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/nao_paga`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Simula a recusa de pagamento de uma reserva (para testes)
- **Ações**: Atualiza status do pagamento para 'rejected' e marca como recusada
- **Retorno**: JSON com `result: 'recusada'`

#### 9. `receberReservas()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/receber_reservas`
- **Parâmetros Query**:
  - `motel` (obrigatório): ID do motel
- **Descrição**: Retorna todas as reservas não processadas de um motel específico
- **Retorno**: JSON com array de reservas, incluindo dados de pagamento

#### 10. `reservaProcessado()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/processado`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Marca uma reserva específica como processada
- **Ações**: Atualiza `processado_rubens` para 'S' e status para 'Aceito'
- **Retorno**: String `'ok'`

#### 11. `confirmarCheckin()`
- **Método HTTP**: GET
- **Endpoint**: `/api/rubens/reserva/checkin`
- **Parâmetros Query**:
  - `codigo` (obrigatório): ID da reserva
- **Descrição**: Confirma o check-in de uma reserva confirmada
- **Ações**: Atualiza status para 'Checkin' e registra a hora do check-in
- **Retorno**: JSON com `result: 'ok'`

---

## Model: Rubens

**Localização**: `application/Models/Rubens/Rubens.php`

### Responsabilidades

O Model `Rubens` é responsável por:
- Interagir diretamente com o banco de dados
- Executar queries SQL complexas
- Gerenciar operações CRUD nas tabelas relacionadas
- Retornar dados estruturados para o Controller

### Métodos do Model

#### 1. `checkMotelRubens($id)`
- **Descrição**: Verifica se um motel existe e está ativo com integração Rubens
- **Retorno**: Objeto Read com resultados da query
- **Query**: Busca usuários com status 'Ativo' e integracao 'rubens'

#### 2. `getSuites()`
- **Descrição**: Busca todas as suítes de todos os motéis com integração Rubens
- **Retorno**: Objeto Read com JSON estruturado hierarquicamente
- **Query**: Usa CTEs (Common Table Expressions) para estruturar motéis e suítes
- **Formato**: JSON aninhado com índices numéricos

#### 3. `updateDisponibilidade($params)`
- **Descrição**: Atualiza a disponibilidade de uma suíte específica
- **Validações**: Verifica existência da suíte antes de atualizar
- **Parâmetros Esperados**:
  - `motel`: ID do motel
  - `suite`: ID da suíte
  - `qtde`: Nova quantidade de disponibilidade
- **Retorno**: `true` em sucesso ou string com erro

#### 4. `getDisponibilidade($params)`
- **Descrição**: Retorna informações de disponibilidade de suítes
- **Parâmetros Esperados**:
  - `motel`: ID do motel (obrigatório)
  - `suite`: ID da suíte (opcional, filtra por suíte específica)
- **Retorno**: Objeto Read com JSON de disponibilidades

#### 5. `criarPreReservaTeste($id_motel, $id_suite)`
- **Descrição**: Cria uma pré-reserva de teste com dados fictícios
- **Dados Criados**:
  - Reserva na tabela `reservas`
  - Pagamento na tabela `pagamentos`
  - Reduz disponibilidade da suíte em 1
- **Retorno**: ID da reserva criada ou `null` em caso de erro

#### 6. `verificarDisponibilidadeSuite($id_motel, $id_suite)`
- **Descrição**: Verifica se uma suíte tem disponibilidade > 0
- **Retorno**: `true` se disponível, `false` caso contrário

#### 7. `getReservaComPagamento($id)`
- **Descrição**: Busca dados completos de uma reserva incluindo pagamento
- **Join**: LEFT JOIN entre tabelas `reservas` e `pagamentos`
- **Retorno**: Array com dados da reserva ou `null` se não encontrada

#### 8. `simularPagamentoReserva($id)`
- **Descrição**: Simula pagamento aprovado de uma reserva
- **Ações**:
  - Atualiza `pagamentos.pagamento_status` para 'approved'
  - Atualiza `reservas.processado_rubens` para 'N'
  - Atualiza `reservas.fase_rubens` para 2
- **Retorno**: `true` se atualizou pelo menos uma linha

#### 9. `simularCancelamentoReserva($id)`
- **Descrição**: Simula cancelamento de uma reserva
- **Ações**:
  - Atualiza `pagamentos.pagamento_status` para 'cancelled'
  - Atualiza `reservas.cancelada_rubens` para 'S'
  - Atualiza `reservas.fase_rubens` para 0
  - Atualiza `reservas.status_reserva` para 'Cancelado'
- **Retorno**: `true` se atualizou pelo menos uma linha

#### 10. `simularNaoPagamentoReserva($id)`
- **Descrição**: Simula recusa de pagamento de uma reserva
- **Ações**:
  - Atualiza `pagamentos.pagamento_status` para 'rejected'
  - Atualiza `reservas.cancelada_rubens` para 'S'
  - Atualiza `reservas.fase_rubens` para 0
  - Atualiza `reservas.status_reserva` para 'Recusado'
- **Retorno**: `true` se atualizou pelo menos uma linha

#### 11. `getReservasNaoProcessadasPorMotel($id_motel)`
- **Descrição**: Busca todas as reservas não processadas de um motel
- **Condições**: `processado_rubens = 'N'` e `integracao = 'rubens'`
- **Join**: LEFT JOIN com tabela `pagamentos`
- **Retorno**: Array de reservas ordenadas por ID decrescente

#### 12. `marcarReservasComoProcessadasPorMotel($id_reserva)`
- **Descrição**: Marca uma reserva específica como processada
- **Ações**:
  - Atualiza `processado_rubens` para 'S'
  - Atualiza `status_reserva` para 'Aceito'
- **Retorno**: Número de linhas afetadas

#### 13. `confirmarCheckinReserva($id_reserva)`
- **Descrição**: Confirma o check-in de uma reserva
- **Ações**:
  - Atualiza `status_reserva` para 'Checkin'
  - Registra `checking_hora` com hora atual (fuso horário de São Paulo)
- **Condição**: Apenas para reservas com status 'Confirmado'

---

## Tabelas do Banco de Dados

### Tabela: `usuarios`
- Armazena informações dos motéis
- Campos relevantes: `id`, `nome`, `status`, `integracao`

### Tabela: `suites`
- Armazena informações das suítes
- Campos relevantes: `id`, `id_motel`, `nome`, `status`, `disponibilidade`

### Tabela: `reservas`
- Armazena informações das reservas
- Campos relevantes:
  - `id`: ID da reserva
  - `id_motel`: ID do motel
  - `id_suite`: ID da suíte
  - `codigo_reserva`: Código único da reserva
  - `processado_rubens`: Flag de processamento ('S' ou 'N')
  - `cancelada_rubens`: Flag de cancelamento ('S' ou 'N')
  - `fase_rubens`: Fase da reserva (0=cancelada, 1=pré-reserva, 2=paga)
  - `status_reserva`: Status da reserva ('Aceito', 'Confirmado', 'Checkin', 'Cancelado', 'Recusado')
  - `integracao`: Tipo de integração ('rubens')
  - `checking_hora`: Hora do check-in

### Tabela: `pagamentos`
- Armazena informações dos pagamentos
- Campos relevantes:
  - `id_reserva`: ID da reserva associada
  - `pagamento_status`: Status do pagamento ('pending', 'approved', 'cancelled', 'rejected')
  - `pagamento_metodo`: Método de pagamento
  - `pagamento_valor`: Valor do pagamento
  - `external_reference`: Referência externa (código da reserva)

---

## Fluxo de Trabalho da Integração

### 1. Criação de Reserva
```
CriarReservaTeste() → criarPreReservaTeste() → 
  [Cria reserva] + [Cria pagamento] + [Reduz disponibilidade]
```

### 2. Simulação de Pagamento
```
reservaPaga() → simularPagamentoReserva() → 
  [Atualiza pagamento_status] + [Atualiza fase_rubens]
```

### 3. Processamento de Reservas
```
receberReservas() → getReservasNaoProcessadasPorMotel() → 
  [Retorna lista de reservas não processadas]
  
reservaProcessado() → marcarReservasComoProcessadasPorMotel() → 
  [Marca reserva como processada]
```

### 4. Check-in
```
confirmarCheckin() → confirmarCheckinReserva() → 
  [Atualiza status_reserva] + [Registra checking_hora]
```

---

## Tratamento de Erros

### Códigos HTTP

- **200**: Sucesso
- **400**: Parâmetros inválidos ou obrigatórios ausentes
- **404**: Recurso não encontrado
- **500**: Erro interno do servidor

### Mensagens de Erro Comuns

- `'Erro: Motel não encontrado'` - Motel não existe ou não tem integração Rubens
- `'Erro: Suíte não encontrada'` - Suíte não existe para o motel especificado
- `'Erro: Parâmetros insuficientes ou inválidos'` - Parâmetros obrigatórios ausentes
- `'Suíte Indisponível'` - Suíte não tem disponibilidade (qtde <= 0)

---

## Observações Importantes

1. **Fuso Horário**: O sistema usa fuso horário de São Paulo (`America/Sao_Paulo`)
2. **Formato de Retorno**: Todas as respostas usam `JSON_UNESCAPED_UNICODE` para suportar caracteres especiais
3. **Segurança**: Todos os parâmetros são validados antes de serem usados em queries
4. **Métodos de Teste**: Vários endpoints são específicos para testes (`CriarReservaTeste`, `reservaPaga`, etc.)
5. **Disponibilidade Atômica**: A redução de disponibilidade é feita de forma atômica para evitar race conditions

---

## Melhorias Sugeridas

1. Implementar autenticação e autorização nos endpoints
2. Migrar métodos de teste (GET) para POST/PUT/DELETE adequados
3. Adicionar rate limiting para prevenir abuso
4. Implementar logging de auditoria
5. Adicionar documentação OpenAPI/Swagger
6. Implementar testes unitários e de integração
7. Adicionar validação mais robusta de dados de entrada
8. Implementar paginação nas listagens de reservas

