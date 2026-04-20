<?php

namespace Agencia\Close\Controllers\Relatorio;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Models\Reserva\ReservaRelatorio;

class RelatorioReservasController extends Controller
{
    public function index($params)
    {
        if (!isset($_SESSION['busca_perfil_tipo']) || (int) $_SESSION['busca_perfil_tipo'] !== 0) {
            $this->router->redirect('/');
            return;
        }

        $this->setParams($params);

        $dataInicio = isset($_GET['data_inicio']) ? trim((string) $_GET['data_inicio']) : '';
        $dataFim = isset($_GET['data_fim']) ? trim((string) $_GET['data_fim']) : '';
        $idMotel = isset($_GET['id_motel']) ? (int) $_GET['id_motel'] : 0;
        $proprietario = isset($_GET['proprietario']) ? trim((string) $_GET['proprietario']) : '';
        $cidade = isset($_GET['cidade']) ? trim((string) $_GET['cidade']) : '';

        if ($dataInicio === '' || $dataFim === '') {
            $dataInicio = date('Y-m-01');
            $dataFim = date('Y-m-d');
        }

        if (!$this->dataValida($dataInicio) || !$this->dataValida($dataFim)) {
            $dataInicio = date('Y-m-01');
            $dataFim = date('Y-m-d');
        }

        if (strtotime($dataInicio) > strtotime($dataFim)) {
            $tmp = $dataInicio;
            $dataInicio = $dataFim;
            $dataFim = $tmp;
        }

        $model = new ReservaRelatorio();
        $idMotelFiltro = $idMotel > 0 ? $idMotel : null;
        $cidadeFiltro = $cidade !== '' ? $cidade : null;
        $proprietarioFiltro = $proprietario !== '' ? $proprietario : null;

        $resumo = $model->getResumoGeral($dataInicio, $dataFim, $idMotelFiltro, $cidadeFiltro, $proprietarioFiltro);
        $resumo['total_repasse_motel'] = (float) ($resumo['total_valor_pago'] ?? 0) - (float) ($resumo['lucro_plataforma'] ?? 0);
        $porMotel = $model->getAgregadoPorMotel($dataInicio, $dataFim, $cidadeFiltro, $proprietarioFiltro);
        if ($idMotelFiltro !== null) {
            $porMotel = array_values(array_filter($porMotel, static function ($row) use ($idMotelFiltro) {
                return (int) $row['id_motel'] === $idMotelFiltro;
            }));
        }

        usort($porMotel, static function ($a, $b) {
            return ((int) ($b['qtd_reservas'] ?? 0)) <=> ((int) ($a['qtd_reservas'] ?? 0));
        });

        $itens = $model->getReservasPagasSucesso($dataInicio, $dataFim, $idMotelFiltro, $cidadeFiltro, $proprietarioFiltro);
        foreach ($itens as $k => $row) {
            $pago = (float) ($row['pagamento_valor'] ?? 0);
            $contrato = (float) ($row['contrato'] ?? 0);
            $repasse = $pago * ((100 - $contrato) / 100);
            $itens[$k]['repasse_motel'] = $repasse;
        }

        $export = isset($_GET['export']) ? trim((string) $_GET['export']) : '';
        if ($export === '1') {
            $filename = 'relatorio-reservas-' . $dataInicio . '_a_' . $dataFim . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID',
                'Código',
                'Cliente',
                'Telefone',
                'Motel',
                'Cidade',
                'Suíte',
                'Data Reserva',
                'Chegada',
                'Período',
                'Valor Reserva',
                'Total Pago (aprovado)',
                'Contrato %',
                'Repasse Motel',
                'Data Pagamento',
            ], ';');
            foreach ($itens as $row) {
                fputcsv($out, [
                    $row['id'] ?? '',
                    $row['codigo_reserva'] ?? '',
                    $row['nome'] ?? '',
                    $row['telefone'] ?? '',
                    $row['motel_nome'] ?? '',
                    $row['cidade_motel'] ?? '',
                    $row['suite_nome'] ?? '',
                    $row['data_reserva'] ?? '',
                    $row['chegada_reserva'] ?? '',
                    $row['periodo_reserva'] ?? '',
                    $row['valor_reserva'] ?? '',
                    $row['pagamento_valor'] ?? '',
                    $row['contrato'] ?? '',
                    isset($row['repasse_motel']) ? number_format((float) $row['repasse_motel'], 2, ',', '.') : '',
                    $row['data_pagamento'] ?? '',
                ], ';');
            }
            fclose($out);
            return;
        }

        $moteis = new Moteis();
        $listaMoteis = $moteis->getMoteisList()->getResult() ?: [];
        $listaCidades = $model->getCidadesMotels();
        $listaProprietarios = $model->getProprietariosMotels();

        $this->render('pages/relatorio/reservas.twig', [
            'menu' => 'relatorio_reservas',
            'resumo' => $resumo,
            'por_motel' => $porMotel,
            'itens' => $itens,
            'lista_moteis' => $listaMoteis,
            'lista_cidades' => $listaCidades,
            'lista_proprietarios' => $listaProprietarios,
            'filtro_data_inicio' => $dataInicio,
            'filtro_data_fim' => $dataFim,
            'filtro_id_motel' => $idMotel,
            'filtro_proprietario' => $proprietario,
            'filtro_cidade' => $cidade,
        ]);
    }

    private function dataValida(string $data): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
}
