<?php

namespace Agencia\Close\Models\Reserva;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class ReservaRelatorio extends Model
{
    /**
     * Cidades distintas dos motéis (para filtro do relatório).
     */
    public function getCidadesMotels(): array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT DISTINCT TRIM(u.cidade) AS cidade
            FROM usuarios u
            WHERE u.tipo = '2'
            AND u.cidade IS NOT NULL
            AND TRIM(u.cidade) <> ''
            ORDER BY cidade ASC"
        );
        $rows = $read->getResult() ?: [];
        return array_values(array_filter(array_column($rows, 'cidade')));
    }

    /**
     * Resumo geral no período (admin).
     */
    public function getResumoGeral(string $dataInicio, string $dataFim, ?int $idMotel = null, ?string $cidade = null, ?int $planoRedes = null): array
    {
        $read = new Read();
        $params = "data_inicio={$dataInicio}&data_fim={$dataFim}";
        $filtroMotel = '';
        if ($idMotel !== null && $idMotel > 0) {
            $filtroMotel = ' AND r.id_motel = :id_motel';
            $params .= "&id_motel={$idMotel}";
        }
        $filtroCidade = '';
        if ($cidade !== null && $cidade !== '') {
            $filtroCidade = ' AND u.cidade = :cidade';
            $params .= '&cidade=' . rawurlencode($cidade);
        }
        $filtroPlanoRedes = '';
        if ($planoRedes !== null) {
            $filtroPlanoRedes = ' AND COALESCE(u.plano_redes, 0) = :plano_redes';
            $params .= "&plano_redes={$planoRedes}";
        }

        $read->FullRead(
            "SELECT 
                COUNT(*) AS qtd_reservas,
                COALESCE(SUM(sub.valor_reserva), 0) AS total_valor_reservas,
                COALESCE(SUM(sub.total_pago), 0) AS total_valor_pago,
                COUNT(*) AS qtd_pagamentos_aprovados
            FROM (
                SELECT 
                    r.id,
                    MAX(CAST(r.valor_reserva AS DECIMAL(12,2))) AS valor_reserva,
                    SUM(CAST(p.pagamento_valor AS DECIMAL(12,2))) AS total_pago
                FROM reservas r
                INNER JOIN usuarios u ON u.id = r.id_motel
                INNER JOIN pagamentos p ON p.id_reserva = r.id
                WHERE DATE(r.date_create) BETWEEN :data_inicio AND :data_fim
                AND p.pagamento_status = 'approved'
                AND (p.id_user IS NULL OR p.id_user <> '1')
                AND (r.id_user IS NULL OR r.id_user <> '1')
                {$filtroMotel}
                {$filtroCidade}
                {$filtroPlanoRedes}
                GROUP BY r.id
            ) sub",
            $params
        );
        $row = $read->getResultSingle();
        if (!$row || !isset($row['qtd_reservas'])) {
            $row = [
                'qtd_reservas' => 0,
                'total_valor_reservas' => 0,
                'total_valor_pago' => 0,
                'qtd_pagamentos_aprovados' => 0,
            ];
        }

        $lucro = $this->calcularLucroPlataformaPeriodo($dataInicio, $dataFim, $idMotel, $cidade, $planoRedes);

        return [
            'qtd_reservas' => (int) ($row['qtd_reservas'] ?? 0),
            'total_valor_reservas' => (float) ($row['total_valor_reservas'] ?? 0),
            'qtd_pagamentos_aprovados' => (int) ($row['qtd_pagamentos_aprovados'] ?? 0),
            'total_valor_pago' => (float) ($row['total_valor_pago'] ?? 0),
            'lucro_plataforma' => $lucro,
        ];
    }

    /**
     * Lucro da plataforma (percentual contrato sobre pagamentos aprovados), mesmo critério de Home::getLucroTotal.
     */
    private function calcularLucroPlataformaPeriodo(string $dataInicio, string $dataFim, ?int $idMotel = null, ?string $cidade = null, ?int $planoRedes = null): float
    {
        $read = new Read();
        $params = "data_inicio={$dataInicio}&data_fim={$dataFim}";
        $filtroMotel = '';
        if ($idMotel !== null && $idMotel > 0) {
            $filtroMotel = ' AND r.id_motel = :id_motel';
            $params .= "&id_motel={$idMotel}";
        }
        $filtroCidade = '';
        if ($cidade !== null && $cidade !== '') {
            $filtroCidade = ' AND u.cidade = :cidade';
            $params .= '&cidade=' . rawurlencode($cidade);
        }
        $filtroPlanoRedes = '';
        if ($planoRedes !== null) {
            $filtroPlanoRedes = ' AND COALESCE(u.plano_redes, 0) = :plano_redes';
            $params .= "&plano_redes={$planoRedes}";
        }

        $read->FullRead(
            "SELECT p.pagamento_valor, u.contrato
            FROM pagamentos p
            JOIN usuarios u ON u.id = p.id_motel
            JOIN reservas r ON r.id = p.id_reserva
            WHERE p.pagamento_status = 'approved'
            AND (p.id_user IS NULL OR p.id_user <> '1')
            AND (r.id_user IS NULL OR r.id_user <> '1')
            AND DATE(r.date_create) BETWEEN :data_inicio AND :data_fim
            {$filtroMotel}
            {$filtroCidade}
            {$filtroPlanoRedes}",
            $params
        );
        $result = $read->getResult();
        $lucro = 0.0;
        if ($result) {
            foreach ($result as $row) {
                $valor = floatval(str_replace(',', '.', preg_replace('/\.(?=\d{3},)/', '', (string) $row['pagamento_valor'])));
                $contrato = floatval($row['contrato']);
                $lucro += $valor * ($contrato / 100);
            }
        }
        return $lucro;
    }

    /**
     * Agregados por motel no período (uma linha por reserva no subselect, depois soma por motel).
     * total_valor_pago = soma dos pagamentos aprovados de todas as reservas daquele motel nos filtros.
     */
    public function getAgregadoPorMotel(string $dataInicio, string $dataFim, ?string $cidade = null, ?int $planoRedes = null): array
    {
        $params = "data_inicio={$dataInicio}&data_fim={$dataFim}";
        $filtroCidade = '';
        if ($cidade !== null && $cidade !== '') {
            $filtroCidade = ' AND u.cidade = :cidade';
            $params .= '&cidade=' . rawurlencode($cidade);
        }
        $filtroPlanoRedes = '';
        if ($planoRedes !== null) {
            $filtroPlanoRedes = ' AND COALESCE(u.plano_redes, 0) = :plano_redes';
            $params .= "&plano_redes={$planoRedes}";
        }

        $read = new Read();
        $read->FullRead(
            "SELECT 
                u.id AS id_motel,
                u.nome AS motel_nome,
                COUNT(sub.id_reserva) AS qtd_reservas,
                COALESCE(SUM(sub.valor_reserva), 0) AS total_valor_reservas,
                COALESCE(SUM(sub.total_pago_reserva), 0) AS total_valor_pago,
                COALESCE(SUM(sub.lucro_reserva), 0) AS lucro_plataforma
            FROM (
                SELECT 
                    r.id AS id_reserva,
                    r.id_motel,
                    MAX(CAST(r.valor_reserva AS DECIMAL(12,2))) AS valor_reserva,
                    SUM(CAST(p.pagamento_valor AS DECIMAL(12,2))) AS total_pago_reserva,
                    SUM(
                        CAST(p.pagamento_valor AS DECIMAL(12,2))
                        * (CAST(u.contrato AS DECIMAL(12,2)) / 100)
                    ) AS lucro_reserva
                FROM reservas r
                INNER JOIN usuarios u ON u.id = r.id_motel
                INNER JOIN pagamentos p ON p.id_reserva = r.id
                WHERE DATE(r.date_create) BETWEEN :data_inicio AND :data_fim
                AND p.pagamento_status = 'approved'
                AND (p.id_user IS NULL OR p.id_user <> '1')
                AND (r.id_user IS NULL OR r.id_user <> '1')
                {$filtroCidade}
                {$filtroPlanoRedes}
                GROUP BY r.id, r.id_motel
            ) sub
            INNER JOIN usuarios u ON u.id = sub.id_motel
            GROUP BY u.id, u.nome
            ORDER BY u.nome ASC",
            $params
        );

        return $read->getResult() ?: [];
    }

    /**
     * Todas as reservas com pagamento aprovado no período (sucesso), ordenadas por ID decrescente.
     */
    public function getReservasPagasSucesso(string $dataInicio, string $dataFim, ?int $idMotel = null, ?string $cidade = null, ?int $planoRedes = null): array
    {
        $read = new Read();
        $params = "data_inicio={$dataInicio}&data_fim={$dataFim}";
        $filtroMotel = '';
        if ($idMotel !== null && $idMotel > 0) {
            $filtroMotel = ' AND r.id_motel = :id_motel';
            $params .= "&id_motel={$idMotel}";
        }
        $filtroCidade = '';
        if ($cidade !== null && $cidade !== '') {
            $filtroCidade = ' AND u.cidade = :cidade';
            $params .= '&cidade=' . rawurlencode($cidade);
        }
        $filtroPlanoRedes = '';
        if ($planoRedes !== null) {
            $filtroPlanoRedes = ' AND COALESCE(u.plano_redes, 0) = :plano_redes';
            $params .= "&plano_redes={$planoRedes}";
        }

        $read->FullRead(
            "SELECT 
                r.id,
                r.nome,
                r.telefone,
                r.valor_reserva,
                r.status_reserva,
                r.date_create,
                r.data_reserva,
                r.chegada_reserva,
                r.periodo_reserva,
                u.nome AS motel_nome,
                u.cidade AS cidade_motel,
                s.nome AS suite_nome,
                SUM(CAST(p.pagamento_valor AS DECIMAL(12,2))) AS pagamento_valor,
                MAX(p.date_create) AS data_pagamento
            FROM reservas r
            INNER JOIN usuarios u ON u.id = r.id_motel
            INNER JOIN suites s ON s.id = r.id_suite
            INNER JOIN pagamentos p ON p.id_reserva = r.id
            WHERE p.pagamento_status = 'approved'
            AND (p.id_user IS NULL OR p.id_user <> '1')
            AND (r.id_user IS NULL OR r.id_user <> '1')
            AND DATE(r.date_create) BETWEEN :data_inicio AND :data_fim
            {$filtroMotel}
            {$filtroCidade}
            {$filtroPlanoRedes}
            GROUP BY r.id, r.nome, r.telefone, r.valor_reserva, r.status_reserva, r.date_create, r.data_reserva,
                r.chegada_reserva, r.periodo_reserva, u.nome, u.cidade, s.nome
            ORDER BY r.id DESC",
            $params
        );
        return $read->getResult() ?: [];
    }
}
