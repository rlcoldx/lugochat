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
        $planoRedes = isset($_GET['plano_redes']) ? trim((string) $_GET['plano_redes']) : '';
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
        $planoRedesFiltro = null;
        if ($planoRedes === '1') {
            $planoRedesFiltro = 1;
        } elseif ($planoRedes === '0') {
            $planoRedesFiltro = 0;
        }

        $resumo = $model->getResumoGeral($dataInicio, $dataFim, $idMotelFiltro, $cidadeFiltro, $planoRedesFiltro);
        $porMotel = $model->getAgregadoPorMotel($dataInicio, $dataFim, $cidadeFiltro, $planoRedesFiltro);
        if ($idMotelFiltro !== null) {
            $porMotel = array_values(array_filter($porMotel, static function ($row) use ($idMotelFiltro) {
                return (int) $row['id_motel'] === $idMotelFiltro;
            }));
        }

        usort($porMotel, static function ($a, $b) {
            return ((int) ($b['qtd_reservas'] ?? 0)) <=> ((int) ($a['qtd_reservas'] ?? 0));
        });

        $moteis = new Moteis();
        $listaMoteis = $moteis->getMoteisList()->getResult() ?: [];
        $listaCidades = $model->getCidadesMotels();

        $this->render('pages/relatorio/reservas.twig', [
            'menu' => 'relatorio_reservas',
            'resumo' => $resumo,
            'por_motel' => $porMotel,
            'lista_moteis' => $listaMoteis,
            'lista_cidades' => $listaCidades,
            'filtro_data_inicio' => $dataInicio,
            'filtro_data_fim' => $dataFim,
            'filtro_id_motel' => $idMotel,
            'filtro_plano_redes' => $planoRedes,
            'filtro_cidade' => $cidade,
        ]);
    }

    private function dataValida(string $data): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
}
