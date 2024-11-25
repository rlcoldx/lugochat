<?php
function converterDiaSemana($dia) {
    $dias_semana_br = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
    $dias_semana_en = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

    $index = array_search($dia, $dias_semana_br);

    if ($index !== false) {
        return $dias_semana_en[$index];
    }
}

// Função de comparação para ordenar por disponibilidade ASC
function compararDisponibilidade($a, $b) {
    return strcmp($a['disponibilidade'], $b['disponibilidade']);
}

function ClearSearch($pesquisa){
    $palavras = array("da","de","di","do","du","para","pra","em","in","por","até","ate");
    return preg_replace('/\b('.implode('|',$palavras).')\b/','',$pesquisa);
}

function diaSemana($dataAtual)
{
    // Obtém o dia da semana em inglês
    $diaDaSemanaEmIngles = date('D', strtotime($dataAtual));

    // Mapeia os nomes em inglês para os nomes em português
    $traducaoDiasDaSemana = array(
        'Mon' => 'seg',
        'Tue' => 'ter',
        'Wed' => 'qua',
        'Thu' => 'qui',
        'Fri' => 'sex',
        'Sat' => 'sab',
        'Sun' => 'dom'
    );
    
    // Obtém o dia da semana em português usando o array de tradução
    return $traducaoDiasDaSemana[$diaDaSemanaEmIngles];
}

function todosPreenchidos($dados) {
    foreach ($dados as $key => $value) {
        if (empty($value)) {
            return false;
        }
    }
    return true;
}

function gerarCodigoPedido($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function limparCPF($cpf) {
    $cpfLimpo = preg_replace('/\D/', '', $cpf);
    return $cpfLimpo;
}


function traduzirStatusPagamento($status) {
    $statusTraduzido = [
        'approved' => 'Aprovado',
        'pending' => 'Pendente',
        'in_process' => 'Em Processo',
        'rejected' => 'Rejeitado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reembolsado',
        'in_mediation' => 'Em Mediação',
        'charged_back' => 'Estornado',
        'Pendente' => 'Aprovação Pendente',
        'Aceito' => 'Aguardando Pagamento',
        'Recusado' => 'Reserva Recusada',
    ];

    return $statusTraduzido[$status] ?? 'Status desconhecido';
}

function corStatusPagamento($status) {
    $classeBadge = [
        'approved' => 'success',
        'pending' => 'warning',
        'in_process' => 'info',
        'rejected' => 'danger',
        'cancelled' => 'secondary',
        'refunded' => 'primary',
        'in_mediation' => 'info',
        'charged_back' => 'dark',
        'Pendente' => 'warning',
        'Aceito' => 'info',
        'Recusado' => 'danger',
    ];

    return $classeBadge[$status] ?? 'dark';
}