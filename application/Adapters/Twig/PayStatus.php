<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PayStatus extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('payStatus', [$this, 'payStatus']),
        ];
    }

    public function payStatus($status): string
    {
        switch ($status) {
            case '1': $return = '<div class="badge badge-pill badge-light-warning" title="A transação foi iniciada, mas até o momento a Prevenge não recebeu nenhuma informação sobre o pagamento.">Aguardando pagamento</div>'; break;
            case '2': $return = '<div class="badge badge-pill badge-light-warning" title="O comprador optou por pagar com um cartão de crédito e a Prevenge está analisando o risco da transação.">Em análise</div>'; break;
            case '3': $return = '<div class="badge badge-pill badge-light-success" title="A transação foi paga pelo comprador e a Prevenge já recebeu uma confirmação da instituição financeira responsável pelo processamento.">Paga</div>'; break;
            case '4': $return = '<div class="badge badge-pill badge-light-success" title="A transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta.">Disponível</div>'; break;
            case '5': $return = '<div class="badge badge-pill badge-light-warning" title="O comprador, dentro do prazo de liberação da transação, abriu uma disputa.">Em disputa</div>'; break;
            case '6': $return = '<div class="badge badge-pill badge-light-danger" title="O valor da transação foi devolvido para o comprador.">Devolvida</div>'; break;
            case '7': $return = '<div class="badge badge-pill badge-light-danger" title="A transação foi cancelada sem ter sido finalizada.">Cancelada</div>'; break;
        }

        return $return;
    }
}