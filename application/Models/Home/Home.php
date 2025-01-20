<?php

namespace Agencia\Close\Models\Home;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;


class Home extends Model 
{

    public function getTotalReservas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT 
        SUM(CASE WHEN p.pagamento_status = 'approved' THEN 1 ELSE 0 END) AS total_reservas_aprovadas,
        SUM(CASE  WHEN (p.pagamento_status IS NULL OR p.pagamento_status != 'approved') AND r.status_reserva = 'Aceito' THEN 1  ELSE 0 END ) AS total_reservas_nao_concluidas,
        SUM(CASE WHEN r.status_reserva = 'Recusado' THEN 1 ELSE 0 END) AS total_reservas_recusadas
        FROM reservas r
        LEFT JOIN pagamentos p ON r.id = p.id_reserva");
        return $this->read;
    }

    public function getTotalValorMes(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT 
            SUM(CAST(p.pagamento_valor AS DECIMAL(10, 2))) AS total_vendas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE 
            p.pagamento_status = 'approved' 
            AND MONTH(p.date_create) = MONTH(CURRENT_DATE()) 
            AND YEAR(p.date_create) = YEAR(CURRENT_DATE())");
        return $this->read;
    }

    public function getSuidesReservadas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT 
            sub.total_reservas,
            s.nome AS nome_suite,
            MIN(sp.valor) AS menor_valor,
            img.imagem
        FROM 
            (SELECT r.id_suite, COUNT(r.id_suite) AS total_reservas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE p.pagamento_status = 'approved'
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
            sub.total_reservas DESC");
        return $this->read;
    }

    public function getTotalValor(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT 
            SUM(CAST(p.pagamento_valor AS DECIMAL(10, 2))) AS total_vendas
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE 
            p.pagamento_status = 'approved'");
        return $this->read;
    }

    public function getRegistrosPorDiaDaSemana(): array
    {
        // Array base para todos os dias da semana em português
        $diasSemana = [
            'Domingo' => 0,
            'Segunda-feira' => 0,
            'Terça-feira' => 0,
            'Quarta-feira' => 0,
            'Quinta-feira' => 0,
            'Sexta-feira' => 0,
            'Sábado' => 0
        ];

        // Executa a consulta considerando apenas reservas pagas
        $this->read->FullRead("
            SELECT DAYOFWEEK(r.date_create) AS dia_semana_num, COUNT(*) AS quantidade
            FROM reservas r
            JOIN pagamentos p ON r.id = p.id_reserva
            WHERE p.pagamento_status = 'approved'
            GROUP BY dia_semana_num
            ORDER BY dia_semana_num
        ");

        // Mapeamento de números para dias da semana em português
        $mapaDias = [
            1 => 'Domingo',
            2 => 'Segunda-feira',
            3 => 'Terça-feira',
            4 => 'Quarta-feira',
            5 => 'Quinta-feira',
            6 => 'Sexta-feira',
            7 => 'Sábado'
        ];

        // Obtém os resultados da consulta
        $result = $this->read->getResult();

        // Atualiza o array base com as quantidades retornadas pela consulta
        if ($result) {
            foreach ($result as $row) {
                $diaSemanaPort = $mapaDias[$row['dia_semana_num']];
                $diasSemana[$diaSemanaPort] = (int)$row['quantidade'];
            }
        }

        // Retorna os dados como um array de dias e quantidades
        $dados = [];
        foreach ($diasSemana as $dia => $quantidade) {
            $dados[] = [
                'dia_da_semana' => $dia,
                'quantidade' => $quantidade
            ];
        }

        return $dados;
    }



}