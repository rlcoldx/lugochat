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

function getNomeEstado($sigla) {
    $estados = [
        "AC" => "Acre",
        "AL" => "Alagoas",
        "AP" => "Amapá",
        "AM" => "Amazonas",
        "BA" => "Bahia",
        "CE" => "Ceará",
        "DF" => "Distrito Federal",
        "ES" => "Espírito Santo",
        "GO" => "Goiás",
        "MA" => "Maranhão",
        "MT" => "Mato Grosso",
        "MS" => "Mato Grosso do Sul",
        "MG" => "Minas Gerais",
        "PA" => "Pará",
        "PB" => "Paraíba",
        "PR" => "Paraná",
        "PE" => "Pernambuco",
        "PI" => "Piauí",
        "RJ" => "Rio de Janeiro",
        "RN" => "Rio Grande do Norte",
        "RS" => "Rio Grande do Sul",
        "RO" => "Rondônia",
        "RR" => "Roraima",
        "SC" => "Santa Catarina",
        "SP" => "São Paulo",
        "SE" => "Sergipe",
        "TO" => "Tocantins"
    ];

    $sigla = strtoupper($sigla); // Converter a sigla para maiúsculas, caso esteja em minúsculas

    if(array_key_exists($sigla, $estados)) {
        return $estados[$sigla];
    } else {
        return $sigla;
    }
}

/**
 * Normaliza string de hora para HH:MM (24 horas).
 * - AM/PM: "07:00 PM" => "19:00", "7:00 am" => "07:00"
 * - Formato "N h MM": "18 h 00", "17h00" => "18:00", "17:00"
 * - Já em 24h: "9:5" / "09:05" / "9:05:00" => "09:05"
 *
 * @param string|null $hora
 * @return string HH:MM ou string vazia se não for possível interpretar
 */
function converterHoraPara24h($hora) {
    if ($hora === null) {
        return '';
    }
    $s = trim((string) $hora);
    if ($s === '') {
        return '';
    }

    // 1) "18 h 00" / "17h00" / "3 h 5" → minutos com até 2 dígitos; sem AM/PM
    $semEspaco = preg_replace('/\s+/', '', $s);
    if (is_string($semEspaco) && $semEspaco !== '' && !preg_match('/(am|pm|a\.m|p\.m)\.?$/i', $semEspaco)
        && preg_match('/^(\d{1,2})h(\d{1,2})$/i', $semEspaco, $hMatch)) {
        $h = (int) $hMatch[1];
        $m = (int) $hMatch[2];
        if ($h >= 0 && $h <= 23 && $m >= 0 && $m <= 59) {
            return sprintf('%02d:%02d', $h, $m);
        }
    }

    // 2) AM/PM
    if (preg_match('/\b(am|pm|a\.?m|p\.?m)\.?\b/i', $s)) {
        $ts = strtotime($s);
        if ($ts !== false) {
            return date('H:i', $ts);
        }
        $limpo = str_ireplace(['a.m.', 'p.m.', 'a.m', 'p.m', 'a. m.', 'p. m.'], ['am', 'pm', 'am', 'pm', 'am', 'pm'], $s);
        $ts = strtotime($limpo);
        if ($ts !== false) {
            return date('H:i', $ts);
        }
        $dt = \DateTime::createFromFormat('g:i A', $s) ?: \DateTime::createFromFormat('h:i A', $s) ?: \DateTime::createFromFormat('G:i A', $s) ?: \DateTime::createFromFormat('H:i A', $s) ?: \DateTime::createFromFormat('g:i:s A', $s) ?: \DateTime::createFromFormat('h:i:s A', $s) ?: \DateTime::createFromFormat('G:i:s A', $s);
        if ($dt instanceof \DateTime) {
            return $dt->format('H:i');
        }
    }

    // 3) 24h com dois-pontos (e opcionalmente segundos)
    if (preg_match('/^(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $s, $m)) {
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
            return sprintf('%02d:%02d', $h, $min);
        }
    }

    return '';
}