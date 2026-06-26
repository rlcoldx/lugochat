<?php

namespace Agencia\Close\Helpers;

/**
 * Status de pagamento Mercado Pago (campo status da API /v1/payments).
 * @see https://www.mercadopago.com.br/developers/pt/docs/checkout-api-payments/response-handling/query-results
 */
class MercadoPagoPaymentStatus
{
    /**
     * @return array<string, string>
     */
    public static function opcoes(): array
    {
        return [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'authorized' => 'Autorizado',
            'in_process' => 'Em processamento',
            'in_mediation' => 'Em mediação',
            'rejected' => 'Rejeitado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'charged_back' => 'Estornado',
        ];
    }
}
