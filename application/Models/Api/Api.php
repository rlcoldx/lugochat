<?php

namespace Agencia\Close\Models\Api;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Create;
use Agencia\Close\Models\Model;

class Api extends Model 
{

    public function checkMotelApi($id): Read
    {   
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM usuarios WHERE `status` = 'Ativo' AND integracao = 'api' AND id = :id", "id={$id}");
        return $this->read;
    }

    public function getSuites($id_motel): Read
    {
        $this->read = new Read();
        $this->read->FullRead("WITH usuarios_cte AS (
        SELECT ROW_NUMBER() OVER (ORDER BY u.id) - 1 AS u_idx,
                u.id,
                u.nome,
                u.status
        FROM usuarios u
        WHERE u.integracao = 'api'
            AND EXISTS (SELECT 1 FROM suites WHERE id_motel = u.id) AND u.id = :id_motel
        ),
        suites_cte AS (
        SELECT ROW_NUMBER() OVER (PARTITION BY s.id_motel ORDER BY s.id) - 1 AS s_idx,
                s.id_motel,
                s.id,
                s.nome,
                s.status
        FROM suites s
        )
        SELECT JSON_OBJECTAGG(u_idx, user_json) AS json_result
        FROM (
        SELECT u.u_idx,
                JSON_OBJECT(
                'ID', u.id,
                'nome', u.nome,
                'status', CASE WHEN u.status = 'Ativo' THEN 'S' ELSE 'N' END,
                'suites', (
                    SELECT JSON_OBJECTAGG(s.s_idx, JSON_OBJECT(
                                'ID', s.id,
                                'nome', s.nome,
                                'status', CASE WHEN s.status = 'Publicado' THEN 'S' ELSE 'N' END
                                ))
                    FROM suites_cte s
                    WHERE s.id_motel = u.id ORDER BY s.id ASC
                )
                ) AS user_json
        FROM usuarios_cte u
        ) t", "id_motel={$id_motel}");
        return $this->read;
    }

    public function updateDisponibilidade($params)
    {
        if (!isset($params['qtde']) || !isset($params['suite']) || !isset($params['motel'])) {
            return 'Erro: Parâmetros insuficientes ou inválidos.';
        }

        try {
            $this->read = new Read();
            $this->read->ExeRead(
                'suites',
                'WHERE id = :id AND id_motel = :id_motel',
                "id={$params['suite']}&id_motel={$params['motel']}"
            );

            $suíteEncontrada = $this->read->getResult();

            if (empty($suíteEncontrada)) {
                return 'Erro: Suíte não encontrada.';
            }

            // Se encontrou, atualiza
            $this->update = new Update();
            $dados = [
                'disponibilidade' => $params['qtde']
            ];

            $this->update->ExeUpdate(
                'suites',
                $dados,
                'WHERE id = :id AND id_motel = :id_motel',
                "id={$params['suite']}&id_motel={$params['motel']}"
            );

            return true;
            
        } catch (\Exception $e) {
            return 'Erro: ' . $e->getMessage();
        }
    }

    public function getDisponibilidade($params): Read
    {   
        $bysuite = "";
        if(!empty($params['suite'])){
            $bysuite = " AND s.id = '".$params['suite']."' ";
        }
        $this->read = new Read();
        $this->read->FullRead("SELECT JSON_OBJECTAGG(s_idx, suite_json) AS json_result
        FROM (
        SELECT ROW_NUMBER() OVER (ORDER BY s.id) - 1 AS s_idx, JSON_OBJECT('ID', s.id, 'disponibilidade', s.disponibilidade) AS suite_json
        FROM suites s WHERE s.id_motel = :motel $bysuite ) t", "motel={$params['motel']}");
        return $this->read;
    }

    /**
     * Cria uma pré-reserva e um pagamento fictício para testes da integração API.
     * @param int $id_motel
     * @param int $id_suite
     * @return int|null ID da reserva criada ou null em caso de erro
     */
    public function criarPreReservaTeste($id_motel, $id_suite, $inicio, $periodo, $chegada)
    {
        $codigo_reserva = substr(md5(uniqid()), 0, 8);
        // Dados fictícios do cliente
        $dadosReserva = [
            'id_motel' => $id_motel,
            'id_suite' => $id_suite,
            'id_usuario' => '1',
            'nome' => 'João da Silva',
            'cpf' => '111.111.111-11',
            'email' => 'joao.silva@email.com',
            'telefone' => '(11) 99999-9999',
            'fase_api' => 1, // pré-reserva
            'processado_api' => 'N',
            'cancelada_api' => 'N',
            'status_reserva' => 'Pendente',
            'integracao' => 'api',
            'data_reserva' => $inicio,
            'chegada_reserva' => $chegada,
            'periodo_reserva' => $periodo,
            'valor_reserva' => '0.10',
            'codigo_reserva' => $codigo_reserva,
        ];

        $create = new Create();
        $create->ExeCreate('reservas', $dadosReserva);
        $reserva_id = $create->getResult();

        // Cria pagamento fictício
        $dadosPagamento = [
            'id_motel' => $id_motel,
            'id_reserva' => $reserva_id,
            'id_user' => '1',
            'pagamento_metodo' => 'cartao',
            'pagamento_valor' => '0.10',
            'pagamento_parcelas' => '1',
            'pagamento_status' => 'pending',
            'external_reference' => $codigo_reserva
        ];

        $createPagamento = new Create();
        $createPagamento->ExeCreate('pagamentos', $dadosPagamento);

        // Baixa a disponibilidade da suíte em 1 de forma atômica e performática
        $update = new Read();
        $update->FullRead("UPDATE suites SET disponibilidade = disponibilidade - 1 WHERE id = :id AND id_motel = :id_motel AND disponibilidade > 0", "id={$id_suite}&id_motel={$id_motel}");

        return $reserva_id;
    }

    /**
     * Verifica se a suíte está disponível (disponibilidade > 0)
     * @param int $id_motel
     * @param int $id_suite
     * @return bool
     */
    public function verificarDisponibilidadeSuite($id_motel, $id_suite)
    {
        $read = new Read();
        $read->FullRead("SELECT disponibilidade FROM suites WHERE id = :id AND id_motel = :id_motel LIMIT 1", "id={$id_suite}&id_motel={$id_motel}");
        $result = $read->getResult();
        if (!$result || intval($result[0]['disponibilidade']) <= 0) {
            return false;
        }
        return true;
    }

    /**
     * Retorna os dados completos da reserva e do pagamento pelo id da reserva
     * @param int $id
     * @return array|null
     */
    public function getReservaByCodigo($codigo_reserva)
    {
        $read = new Read();
        $read->FullRead("SELECT r.*, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor, p.external_reference
            FROM reservas r
            LEFT JOIN pagamentos p ON p.id_reserva = r.id
            WHERE r.codigo_reserva = :codigo_reserva AND r.integracao = 'api' LIMIT 1", "codigo_reserva={$codigo_reserva}");
        $result = $read->getResultSingle();
        if ($result && isset($result['id'])) {
            return $result['id'];
        }
        return null;
    }

    /**
     * Retorna os dados completos da reserva e do pagamento pelo id da reserva
     * @param int $id
     * @return array|null
     */
    public function getReservaComPagamento($id_reserva)
    {
        $read = new Read();
        $read->FullRead("SELECT r.*, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor, p.external_reference
            FROM reservas r
            LEFT JOIN pagamentos p ON p.id_reserva = r.id
            WHERE r.id = :id_reserva AND r.integracao = 'api' LIMIT 1", "id_reserva={$id_reserva}");
        $result = $read->getResult();
        if ($result && isset($result[0])) {
            return $result[0];
        }
        return null;
    }

    /**
     * Simula o pagamento da reserva: atualiza pagamento_status, processado e fase
     * @param int $id
     * @return bool
     */
    public function simularPagamentoReserva($id_reserva)
    {
        // Atualiza pagamento
        $updatePagamento = new Update();
        $updatePagamento->ExeUpdate(
            'pagamentos',
            ['pagamento_status' => 'approved'],
            'WHERE id_reserva = :id_reserva',
            "id_reserva={$id_reserva}"
        );

        // Atualiza reserva
        $updateReserva = new Update();
        $updateReserva->ExeUpdate(
            'reservas',
            [
                'processado_api' => 'N',
                'cancelada_api' => 'N',
                'status_reserva' => 'Aceito',
                'fase_api' => 2
            ],
            'WHERE id = :id_reserva',
            "id_reserva={$id_reserva}"
        );

        // Verifica se pelo menos uma das atualizações afetou linhas
        return $updatePagamento->getRowCount() > 0 || $updateReserva->getRowCount() > 0;
    }

    /**
     * Simula o cancelamento da reserva: atualiza pagamento_status, cancelada e fase
     * @param int $id
     * @return bool
     */
    public function simularCancelamentoReserva($id_reserva, $id_motel)
    {
        // Atualiza pagamento
        $updatePagamento = new Update();
        $updatePagamento->ExeUpdate(
            'pagamentos',
            ['pagamento_status' => 'cancelled'],
            'WHERE id_reserva = :id_reserva AND id_motel = :id_motel',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );

        // Atualiza reserva
        $updateReserva = new Update();
        $updateReserva->ExeUpdate(
            'reservas',
            [
                'processado_api' => 'S',
                'cancelada_api' => 'S',
                'fase_api' => 0,
                'status_reserva' => 'Cancelado'
            ],
            'WHERE id = :id_reserva AND id_motel = :id_motel',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );

        // Verifica se pelo menos uma das atualizações afetou linhas
        return $updateReserva->getRowCount() > 0;
    }

    /**
     * Simula o NÃO PAGAMENTO da reserva: atualiza pagamento_status, cancelada e fase
     * @param int $id
     * @return bool
     */
    public function simularNaoPagamentoReserva($id_reserva, $id_motel)
    {
        // Atualiza pagamento
        $updatePagamento = new Update();
        $updatePagamento->ExeUpdate(
            'pagamentos',
            ['pagamento_status' => 'rejected'],
            'WHERE id_reserva = :id_reserva AND id_motel = :id_motel',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );

        // Atualiza reserva
        $updateReserva = new Update();
        $updateReserva->ExeUpdate(
            'reservas',
            [
                'processado_api' => 'N',
                'cancelada_api' => 'S',
                'fase_api' => 0,
                'status_reserva' => 'Recusado'
            ],
            'WHERE id = :id_reserva AND id_motel = :id_motel',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );

        // Verifica se pelo menos uma das atualizações afetou linhas
        return $updatePagamento->getRowCount() > 0 || $updateReserva->getRowCount() > 0;
    }

    /**
     * Retorna todas as reservas não processadas para um motel, incluindo dados de pagamento
     * @param int $id_motel
     * @return array
     */
    public function getReservasNaoProcessadasPorMotel($id_motel)
    {
        $read = new Read();
        $read->FullRead(
            "SELECT r.*, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor, p.external_reference FROM reservas AS r LEFT JOIN pagamentos AS p ON p.id_reserva = r.id WHERE r.id_motel = :id_motel AND r.processado_api = 'N' AND r.integracao = 'api' ORDER BY r.id DESC",
            "id_motel={$id_motel}"
        );
        $result = $read->getResult();
        
        // Processa os resultados para substituir valores null pelos valores padrão
        if ($result && is_array($result)) {
            foreach ($result as &$reserva) {
                // Substitui valores null pelos valores padrão
                $reserva['cupom_reserva'] = $reserva['cupom_reserva'] ?? 0;
                $reserva['external_reference'] = $reserva['external_reference'] ?? 0;
                $reserva['checking_hora'] = $reserva['checking_hora'] ?? 'Nao Realizado';
                $reserva['pagamento_status'] = $reserva['pagamento_status'] ?? 'Nao iniciado';
                $reserva['pagamento_metodo'] = $reserva['pagamento_metodo'] ?? 'Nao iniciado';
                $reserva['pagamento_valor'] = $reserva['pagamento_valor'] ?? 0;
            }
            unset($reserva); // Remove a referência do último elemento
        }
        
        return $result;
    }

    /**
     * Marca todas as reservas não processadas de um motel como processadas
     * @param int $id_motel
     * @return int Número de reservas atualizadas
     */
    public function marcarReservasComoProcessadasPorMotel($id_reserva, $id_motel, $status_reserva)
    {
        $update = new Update();
        $update->ExeUpdate(
            'reservas',
            ['processado_api' => 'S', 'status_reserva' => $status_reserva],
            'WHERE id = :id_reserva AND id_motel = :id_motel AND integracao = "api"',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );
        return $update->getRowCount();
    }

    public function confirmarCheckinReserva($id_reserva, $id_motel)
    {
        $update = new Update();

        // Tratamento do fuso horário de São Paulo
        date_default_timezone_set('America/Sao_Paulo');
        $checking_hora = date('H:i:s');

        $update->ExeUpdate(
            'reservas',
            ['status_reserva' => 'Confirmado', 'checking_hora' => $checking_hora],
            'WHERE id = :id_reserva AND id_motel = :id_motel AND status_reserva = "Aceito"',
            "id_reserva={$id_reserva}&id_motel={$id_motel}"
        );
    }

}

