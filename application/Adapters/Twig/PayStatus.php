<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Status de pagamento Mercado Pago (campo status da API /v1/payments).
 * @see https://www.mercadopago.com.br/developers/pt/docs/checkout-api-payments/response-handling/query-results
 */
class PayStatus extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('payStatus', [$this, 'payStatus']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('payStatusOpcoes', [self::class, 'opcoesMercadoPago']),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function opcoesMercadoPago(): array
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

    public function payStatus($status): string
    {
        return self::opcoesMercadoPago()[$status] ?? 'Não iniciado';
    }
}
