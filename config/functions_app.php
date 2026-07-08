<?php

require_once dirname(__DIR__) . '/application/Helpers/MercadoPagoPaymentStatus.php';

use Agencia\Close\Helpers\MercadoPagoPaymentStatus;

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
    $statusTraduzido = MercadoPagoPaymentStatus::opcoes();
    $statusTraduzido['Pendente'] = 'Aprovação Pendente';
    $statusTraduzido['Aceito'] = 'Aguardando Pagamento';
    $statusTraduzido['Recusado'] = 'Reserva Recusada';

    return $statusTraduzido[$status] ?? 'Status desconhecido';
}

function corStatusPagamento($status) {
    $classeBadge = [
        'approved' => 'success',
        'pending' => 'warning',
        'authorized' => 'info',
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

/**
 * Obtém o IP real do cliente, considerando proxies/CDN (Cloudflare) comuns.
 */
function obterIpCliente(): string
{
    $chaves = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($chaves as $chave) {
        if (empty($_SERVER[$chave])) {
            continue;
        }
        // X-Forwarded-For pode conter uma lista: usa o primeiro IP válido.
        foreach (explode(',', $_SERVER[$chave]) as $ip) {
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '';
}

/**
 * Normaliza telefone para apenas dígitos, removendo o código do país (55) quando presente,
 * de modo a comparar números salvos em formatos diferentes (ex: +5511999999999 e 11999999999).
 */
function normalizarTelefone($telefone): string
{
    $digitos = preg_replace('/\D/', '', (string) $telefone);
    if (strlen($digitos) > 11 && strpos($digitos, '55') === 0) {
        $digitos = substr($digitos, 2);
    }
    return $digitos;
}

/**
 * Verifica se um cliente está banido, cruzando id_usuario, e-mail, telefone, CPF e IP.
 * Sempre que um banimento é identificado, o IP atual é registrado para reforçar o bloqueio
 * de novas contas/reservas a partir do mesmo dispositivo/rede.
 *
 * @param PDO   $db    Conexão PDO ativa.
 * @param array $dados Campos disponíveis: id_usuario, email, telefone, cpf.
 * @return bool  TRUE se estiver banido.
 */
function verificarUsuarioBanido($db, array $dados = []): bool
{
    if (!($db instanceof PDO)) {
        return false;
    }

    $idUsuario = isset($dados['id_usuario']) && $dados['id_usuario'] !== '' ? (int) $dados['id_usuario'] : null;
    $email     = isset($dados['email']) ? strtolower(trim($dados['email'])) : '';
    $telefone  = isset($dados['telefone']) ? normalizarTelefone($dados['telefone']) : '';
    $cpf       = isset($dados['cpf']) ? preg_replace('/\D/', '', (string) $dados['cpf']) : '';
    $ip        = obterIpCliente();

    $condicoes = [];
    $params = [];

    if ($idUsuario) {
        $condicoes[] = 'b.id_usuario = :id_usuario';
        $params[':id_usuario'] = $idUsuario;
    }
    if ($email !== '') {
        $condicoes[] = 'LOWER(b.email) = :email';
        $params[':email'] = $email;
    }
    if ($telefone !== '') {
        $condicoes[] = 'b.telefone = :telefone';
        $params[':telefone'] = $telefone;
    }
    if ($cpf !== '') {
        $condicoes[] = 'b.cpf = :cpf';
        $params[':cpf'] = $cpf;
    }

    $idBanido = null;

    if (!empty($condicoes)) {
        $sql = "SELECT b.id FROM usuarios_banidos b
                WHERE b.status = 'ativo' AND (" . implode(' OR ', $condicoes) . ")
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($linha['id'])) {
            $idBanido = (int) $linha['id'];
        }
    }

    // Bloqueio por IP já associado a algum banimento ativo.
    if (!$idBanido && $ip !== '') {
        $stmt = $db->prepare("SELECT b.id FROM usuarios_banidos b
            INNER JOIN usuarios_banidos_ips i ON i.id_banido = b.id
            WHERE b.status = 'ativo' AND i.ip = :ip LIMIT 1");
        $stmt->execute([':ip' => $ip]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($linha['id'])) {
            $idBanido = (int) $linha['id'];
        }
    }

    if ($idBanido) {
        if ($ip !== '') {
            $ins = $db->prepare("INSERT IGNORE INTO usuarios_banidos_ips (id_banido, ip) VALUES (:id_banido, :ip)");
            $ins->execute([':id_banido' => $idBanido, ':ip' => $ip]);
        }
        return true;
    }

    return false;
}