# Introdução ao Sistema API

## Visão Geral

O Sistema API oferece uma camada REST para integração com o LugoChat, permitindo que ERPs, PMSs e outras plataformas parceiros consultem e atualizem dados de suítes, reservas e disponibilidade em tempo real. Todos os endpoints respondem em JSON e seguem padrões HTTP claros para facilitar a automação.

## Solicitação de Token de Acesso

Para utilizar qualquer endpoint é obrigatório possuir um token ativo vinculado ao seu motel. Solicite seu token entrando em contato pelo WhatsApp **+55 11 97574-5503**. Após a validação dos dados, você receberá um token que deve ser mantido em sigilo e usado nas requisições através do parâmetro `motel`.

## Principais Recursos Disponíveis

- **Suítes**: consultar catálogo completo, verificar estoque e atualizar disponibilidade individual.
- **Reservas**: listar reservas pendentes, obter detalhes completos, marcar processamentos e registrar check-in.
- **Cenários de Teste**: criar reservas fictícias, simular pagamentos aprovados, cancelamentos e recusas.

## Autenticação e Segurança

Cada requisição deve incluir o token no parâmetro `motel`. Tokens inválidos ou ausentes geram resposta `401 Unauthorized`. Recomenda-se rotacionar tokens periodicamente e monitorar o contador de acessos para identificar uso indevido.

## Fluxo de Integração Recomendado

1. **Obtenha o token** pelo WhatsApp indicado e configure-o na sua aplicação.
2. **Liste suítes** para armazenar os identificadores necessários no seu sistema.
3. **Receba reservas** utilizando `receber_reservas` em um processo agendado.
4. **Marque reservas como processadas** assim que importar para evitar duplicidade.
5. **Atualize disponibilidade e status** (pagamento, cancelamento, check-in) de acordo com o movimento real.

Em caso de dúvidas adicionais, utilize o mesmo contato de WhatsApp para suporte técnico relacionado à integração.

