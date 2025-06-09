<?php

namespace Agencia\Close\Models\Home;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;


class Home extends Model 
{

    public function getTotalReservas($idMotel = null): Read
    {
        $this->read = new Read();
        $where = '';
        $params = '';
        if ($idMotel) {
            $where = ' WHERE r.id_motel = :id_motel';
            $params = "id_motel={$idMotel}";
        }
        $this->read->FullRead("SELECT 
        SUM(CASE WHEN p.pagamento_status = 'approved' THEN 1 ELSE 0 END) AS total_reservas_aprovadas,
        SUM(CASE  WHEN (p.pagamento_status IS NULL OR p.pagamento_status != 'approved') AND r.status_reserva = 'Aceito' THEN 1  ELSE 0 END ) AS total_reservas_nao_concluidas,
        SUM(CASE WHEN r.status_reserva = 'Recusado' THEN 1 ELSE 0 END) AS total_reservas_recusadas
        FROM reservas r
        LEFT JOIN pagamentos p ON r.id = p.id_reserva" . $where, $params);
        return $this->read;
    }

    public function getTotalValorMes($idMotel = null): Read
    {
        $this->read = new Read();
        $where = '';
        $params = '';
        if ($idMotel) {
            $where = ' AND r.id_motel = :id_motel';
            $params = "id_motel={$idMotel}";
        }
        $this->read->FullRead("SELECT 
            SUM(CAST(p.pagamento_valor AS DECIMAL(10, 2))) AS total_vendas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE 
            p.pagamento_status = 'approved' 
            AND MONTH(p.date_create) = MONTH(CURRENT_DATE()) 
            AND YEAR(p.date_create) = YEAR(CURRENT_DATE())" . $where, $params);
        return $this->read;
    }

    public function getSuidesReservadas($idMotel = null): Read
    {
        $this->read = new Read();
        $where = '';
        $params = '';
        if ($idMotel) {
            $where = ' AND r.id_motel = :id_motel';
            $params = "id_motel={$idMotel}";
        }
        $this->read->FullRead("SELECT 
            sub.total_reservas,
            s.nome AS nome_suite,
            MIN(sp.valor) AS menor_valor,
            img.imagem
        FROM 
            (SELECT r.id_suite, COUNT(r.id_suite) AS total_reservas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE p.pagamento_status = 'approved'" . $where . "
            GROUP BY r.id_suite) AS sub
        JOIN 
            suites s ON sub.id_suite = s.id
        JOIN 
            suites_precos sp ON sub.id_suite = sp.id_suite
        LEFT JOIN 
            suites_imagens img ON sub.id_suite = img.id_suite
        GROUP BY 
            sub.id_suite, s.nome
        ORDER BY 
            sub.total_reservas DESC", $params);
        return $this->read;
    }

    public function getTotalValor($idMotel = null): Read
    {
        $this->read = new Read();
        $where = '';
        $params = '';
        if ($idMotel) {
            $where = ' AND r.id_motel = :id_motel';
            $params = "id_motel={$idMotel}";
        }
        $this->read->FullRead("SELECT 
            SUM(CAST(p.pagamento_valor AS DECIMAL(10, 2))) AS total_vendas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE 
            p.pagamento_status = 'approved'" . $where, $params);
        return $this->read;
    }

    public function getRegistrosPorDiaDaSemana($idMotel = null): array
    {
        $diasSemana = [
            'Domingo' => 0,
            'Segunda-feira' => 0,
            'Terça-feira' => 0,
            'Quarta-feira' => 0,
            'Quinta-feira' => 0,
            'Sexta-feira' => 0,
            'Sábado' => 0
        ];
        $where = '';
        $params = '';
        if ($idMotel) {
            $where = ' AND r.id_motel = :id_motel';
            $params = "id_motel={$idMotel}";
        }
        $this->read = new Read();
        $this->read->FullRead("
            SELECT DAYOFWEEK(r.date_create) AS dia_semana_num, COUNT(*) AS quantidade
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE p.pagamento_status = 'approved'" . $where . "
            GROUP BY dia_semana_num
            ORDER BY dia_semana_num
        ", $params);
        $mapaDias = [
            1 => 'Domingo',
            2 => 'Segunda-feira',
            3 => 'Terça-feira',
            4 => 'Quarta-feira',
            5 => 'Quinta-feira',
            6 => 'Sexta-feira',
            7 => 'Sábado'
        ];
        $result = $this->read->getResult();
        if ($result) {
            foreach ($result as $row) {
                $diaSemanaPort = $mapaDias[$row['dia_semana_num']];
                $diasSemana[$diaSemanaPort] = (int)$row['quantidade'];
            }
        }
        $dados = [];
        foreach ($diasSemana as $dia => $quantidade) {
            $dados[] = [
                'dia_da_semana' => $dia,
                'quantidade' => $quantidade
            ];
        }
        return $dados;
    }

    /**
     * Retorna o lucro total do sistema no mês (soma de todos os pagamentos aprovados do mês, descontando o percentual do motel de cada pagamento)
     * @return float
     */
    public function getLucroMes(): float
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT p.pagamento_valor, u.contrato FROM pagamentos p JOIN usuarios u ON u.id = p.id_motel WHERE p.pagamento_status = 'approved' AND MONTH(p.date_create) = MONTH(CURRENT_DATE()) AND YEAR(p.date_create) = YEAR(CURRENT_DATE())");
        $result = $this->read->getResult();
        $lucro = 0.0;
        if ($result) {
            foreach ($result as $row) {
                $valor = floatval(str_replace(',', '.', preg_replace('/\.(?=\d{3},)/', '', $row['pagamento_valor'])));
                $contrato = floatval($row['contrato']);
                $percentual_sistema = $contrato / 100;
                $lucro += $valor * $percentual_sistema;
            }
        }
        return $lucro;
    }

    /**
     * Retorna o lucro total do sistema (todos os pagamentos aprovados, descontando o percentual do motel de cada pagamento)
     * @return float
     */
    public function getLucroTotal(): float
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT p.pagamento_valor, u.contrato FROM pagamentos p JOIN usuarios u ON u.id = p.id_motel WHERE p.pagamento_status = 'approved'");
        $result = $this->read->getResult();
        $lucro = 0.0;
        if ($result) {
            foreach ($result as $row) {
                $valor = floatval(str_replace(',', '.', preg_replace('/\.(?=\d{3},)/', '', $row['pagamento_valor'])));
                $contrato = floatval($row['contrato']);
                $percentual_sistema = $contrato / 100;
                $lucro += $valor * $percentual_sistema;
            }
        }
        return $lucro;
    }

}