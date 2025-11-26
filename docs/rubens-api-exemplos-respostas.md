# Exemplos de Respostas - Sistema Rubens API

Este documento contém exemplos de respostas da API do Sistema Rubens para facilitar a integração.

---

## 1. Listar Todas as Suítes

**Endpoint**: `GET /api/rubens/suites`

**Resposta**:
```json
{
  "0": {
    "ID": 1,
    "nome": "Motel Exemplo",
    "status": "S",
    "suites": {
      "0": {
        "ID": 5,
        "nome": "Suíte Premium",
        "status": "S"
      },
      "1": {
        "ID": 6,
        "nome": "Suíte Master",
        "status": "S"
      }
    }
  },
  "1": {
    "ID": 2,
    "nome": "Motel Elite",
    "status": "S",
    "suites": {
      "0": {
        "ID": 10,
        "nome": "Suíte Deluxe",
        "status": "S"
      }
    }
  }
}
```

---

## 2. Consultar Disponibilidade

**Endpoint**: `GET /api/rubens/suite/qtde_disp?motel=1&suite=5`

**Resposta**:
```json
{
  "0": {
    "ID": 5,
    "disponibilidade": 3
  },
  "1": {
    "ID": 6,
    "disponibilidade": 5
  }
}
```

**Sem parâmetro suite** (todas as suítes do motel):
```json
{
  "0": {
    "ID": 5,
    "disponibilidade": 3
  }
}
```

---

## 3. Atualizar Disponibilidade

**Endpoint**: `GET /api/rubens/suite/disponibilidade?motel=1&suite=5&qtde=10`

**Resposta de Sucesso**:
```
ok
```

**Resposta de Erro**:
```
Erro: Motel não encontrado
```

**Outros possíveis erros**:
```
Erro: Suíte não encontrada.
Erro: Parâmetros insuficientes ou inválidos.
```

---

## 4. Ver Reserva

**Endpoint**: `GET /api/rubens/reserva/ver?codigo=12345`

**Resposta de Sucesso**:
```json
{
  "id": 12345,
  "id_motel": 1,
  "id_suite": 5,
  "id_usuario": 1,
  "nome": "João da Silva",
  "cpf": "111.111.111-11",
  "email": "joao.silva@email.com",
  "telefone": "(11) 99999-9999",
  "fase_api": 1,
  "processado_api": "N",
  "cancelada_api": "N",
  "status_reserva": "Aceito",
  "integracao": "rubens",
  "data_reserva": "2024-01-15",
  "chegada_reserva": "18:00",
  "periodo_reserva": "4:00",
  "valor_reserva": "100.00",
  "codigo_reserva": "a1b2c3d4",
  "pagamento_status": "pending",
  "pagamento_metodo": "cartao",
  "pagamento_valor": "100.00",
  "external_reference": "a1b2c3d4"
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro codigo é obrigatório."
}
```

**Resposta quando não encontrada**:
```json
{
  "erro": "Reserva não encontrada."
}
```

---

## 5. Listar Reservas Não Processadas

**Endpoint**: `GET /api/rubens/receber_reservas?motel=1`

**Resposta de Sucesso**:
```json
{
  "reservas": [
    {
      "id": 12345,
      "id_motel": 1,
      "id_suite": 5,
      "nome": "João da Silva",
      "cpf": "111.111.111-11",
      "email": "joao.silva@email.com",
      "codigo_reserva": "a1b2c3d4",
      "processado_api": "N",
      "cancelada_api": "N",
      "status_reserva": "Aceito",
      "valor_reserva": "100.00",
      "pagamento_status": "pending",
      "pagamento_metodo": "cartao",
      "pagamento_valor": "100.00"
    },
    {
      "id": 12346,
      "id_motel": 1,
      "id_suite": 6,
      "nome": "Maria Santos",
      "cpf": "222.222.222-22",
      "email": "maria.santos@email.com",
      "codigo_reserva": "e5f6g7h8",
      "processado_api": "N",
      "cancelada_api": "N",
      "status_reserva": "Aceito",
      "valor_reserva": "150.00",
      "pagamento_status": "approved",
      "pagamento_metodo": "cartao",
      "pagamento_valor": "150.00"
    }
  ]
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro motel é obrigatório."
}
```

---

## 6. Marcar Reserva como Processada

**Endpoint**: `GET /api/rubens/reserva/processado?codigo=12345`

**Resposta de Sucesso**:
```
ok
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro código é obrigatório."
}
```

---

## 7. Confirmar Check-in

**Endpoint**: `GET /api/rubens/reserva/checkin?codigo=12345`

**Resposta de Sucesso**:
```json
{
  "result": "ok"
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro código é obrigatório."
}
```

---

## 8. Criar Reserva de Teste

**Endpoint**: `GET /api/rubens/reserva/criar/teste?motel=1&suite=5`

**Resposta de Sucesso**:
```json
{
  "reserva_id": 12345
}
```

**Resposta de Erro - Parâmetros inválidos**:
```json
{
  "erro": "Parâmetros motel e suite são obrigatórios."
}
```

**Resposta de Erro - Suíte indisponível**:
```json
{
  "erro": "Suíte Indisponível."
}
```

**Resposta de Erro - Erro ao criar**:
```json
{
  "erro": "Erro ao criar reserva."
}
```

---

## 9. Simular Pagamento Aprovado

**Endpoint**: `GET /api/rubens/reserva/reserva_paga?codigo=12345`

**Resposta de Sucesso**:
```json
{
  "result": "atualizado"
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro codigo é obrigatório."
}
```

**Resposta quando não encontrada**:
```json
{
  "erro": "Reserva não encontrada ou erro ao atualizar."
}
```

---

## 10. Simular Cancelamento

**Endpoint**: `GET /api/rubens/reserva/cancelar?codigo=12345`

**Resposta de Sucesso**:
```json
{
  "result": "cancelada"
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro codigo é obrigatório."
}
```

**Resposta quando não encontrada**:
```json
{
  "erro": "Reserva não encontrada ou erro ao atualizar."
}
```

---

## 11. Simular Não Pagamento

**Endpoint**: `GET /api/rubens/reserva/nao_paga?codigo=12345`

**Resposta de Sucesso**:
```json
{
  "result": "recusada"
}
```

**Resposta de Erro**:
```json
{
  "erro": "Parâmetro codigo é obrigatório."
}
```

**Resposta quando não encontrada**:
```json
{
  "erro": "Reserva não encontrada ou erro ao atualizar."
}
```

---

## Códigos de Status HTTP

### 200 - Sucesso
Retornado quando a operação é concluída com sucesso.

### 400 - Bad Request
Retornado quando há parâmetros inválidos ou obrigatórios ausentes.

**Exemplos**:
- Parâmetros obrigatórios não fornecidos
- Parâmetros com valores inválidos (ex: ID <= 0)

### 404 - Not Found
Retornado quando o recurso solicitado não foi encontrado.

**Exemplos**:
- Reserva não encontrada
- Motel não encontrado

### 500 - Internal Server Error
Retornado quando ocorre um erro interno do servidor.

**Exemplos**:
- Erro ao criar reserva
- Erro de conexão com banco de dados

---

## Fluxo Completo de Teste

### 1. Criar uma reserva de teste
```bash
GET /api/rubens/reserva/criar/teste?motel=1&suite=5
```
**Resposta**: `{"reserva_id": 12345}`

### 2. Ver detalhes da reserva criada
```bash
GET /api/rubens/reserva/ver?codigo=12345
```
**Resposta**: Dados completos da reserva

### 3. Simular pagamento aprovado
```bash
GET /api/rubens/reserva/reserva_paga?codigo=12345
```
**Resposta**: `{"result": "atualizado"}`

### 4. Verificar atualização
```bash
GET /api/rubens/reserva/ver?codigo=12345
```
**Resposta**: Status agora é `fase_api: 2` e `pagamento_status: approved`

### 5. Confirmar check-in
```bash
GET /api/rubens/reserva/checkin?codigo=12345
```
**Resposta**: `{"result": "ok"}`

### 6. Verificar status final
```bash
GET /api/rubens/reserva/ver?codigo=12345
```
**Resposta**: Status agora é `status_reserva: Checkin` com `checking_hora` preenchido

---

## Observações Importantes

1. **Todos os IDs são numéricos inteiros**
2. **Valores monetários são strings** (ex: "100.00")
3. **Datas seguem formato** `YYYY-MM-DD`
4. **Horas seguem formato** `HH:MM:SS`
5. **Flags usam** `'S'` ou `'N'` para sim/não
6. **Status de pagamento**: `pending`, `approved`, `cancelled`, `rejected`
7. **Status de reserva**: `Aceito`, `Confirmado`, `Checkin`, `Cancelado`, `Recusado`
8. **Fase Rubens**: 
   - `0` = Cancelada
   - `1` = Pré-reserva
   - `2` = Paga

---

## Variáveis de Ambiente Recomendadas

Para uso no Postman ou desenvolvimento:

```
base_url = http://localhost/lugochat
motel_id = 1
suite_id = 5
```

