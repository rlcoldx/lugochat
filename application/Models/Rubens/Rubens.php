<?php

namespace Agencia\Close\Models\Rubens;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Create;
use Agencia\Close\Models\Model;

class Rubens extends Model 
{

    public function checkMotelRubens($id): Read
    {   
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM usuarios WHERE `status` = 'Ativo' AND integracao = 'rubens' AND id = :id", "id={$id}");
        return $this->read;
    }

    public function getSuites(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("WITH usuarios_cte AS (
        SELECT ROW_NUMBER() OVER (ORDER BY u.id) - 1 AS u_idx,
                u.id,
                u.nome,
                u.status
        FROM usuarios u
        WHERE u.integracao = 'rubens'
            AND EXISTS (SELECT 1 FROM suites WHERE id_motel = u.id)
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
        ) t");
        return $this->read;
    }

    public function updateDisponibilidade($params)
    {
        if (empty($params['qtde']) || empty($params['suite']) || empty($params['motel'])) {
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
     * Cria uma pré-reserva e um pagamento fictício para testes da integração Rubens.
     * @param int $id_motel
     * @param int $id_suite
     * @return int|null ID da reserva criada ou null em caso de erro
     */
    public function criarPreReservaTeste($id_motel, $id_suite)
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
            'fase_rubens' => 1, // pré-reserva
            'processado_rubens' => 'N',
            'cancelada_rubens' => 'N',
            'status_reserva' => 'Aceito',
            'integracao' => 'rubens',
            'data_reserva' => date('Y-m-d'),
            'chegada_reserva' => '18:00',
            'periodo_reserva' => '4h',
            'valor_reserva' => '100.00',
            'codigo_reserva' => $codigo_reserva,
        ];

        $create = new Create();
        $create->ExeCreate('reservas', $dadosReserva);
        $reserva_id = $create->getResult();

        if (!$reserva_id) {
            return null;
        }

        // Cria pagamento fictício
        $dadosPagamento = [
            'id_motel' => $id_motel,
            'id_reserva' => $reserva_id,
            'id_user' => '1',
            'pagamento_metodo' => 'cartao',
            'pagamento_valor' => '100.00',
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
    public function getReservaComPagamento($id)
    {
        $read = new Read();
        $read->FullRead("SELECT r.*, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor, p.external_reference
            FROM reservas r
            LEFT JOIN pagamentos p ON p.id_reserva = r.id
            WHERE r.id = :id AND r.integracao = 'rubens' LIMIT 1", "id={$id}");
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
    public function simularPagamentoReserva($id)
    {
        // Atualiza pagamento
        $updatePagamento = new Update();
        $updatePagamento->ExeUpdate(
            'pagamentos',
            ['pagamento_status' => 'approved'],
            'WHERE id_reserva = :id_reserva',
            "id_reserva={$id}"
        );

        // Atualiza reserva
        $updateReserva = new Update();
        $updateReserva->ExeUpdate(
            'reservas',
            [
                'processado_rubens' => 'S',
                'fase_rubens' => 2
            ],
            'WHERE id = :id',
            "id={$id}"
        );

        // Verifica se pelo menos uma das atualizações afetou linhas
        return $updatePagamento->getRowCount() > 0 || $updateReserva->getRowCount() > 0;
    }

    /**
     * Simula o cancelamento da reserva: atualiza pagamento_status, cancelada e fase
     * @param int $id
     * @return bool
     */
    public function simularCancelamentoReserva($id)
    {
        // Atualiza pagamento
        $updatePagamento = new Update();
        $updatePagamento->ExeUpdate(
            'pagamentos',
            ['pagamento_status' => 'cancelled'],
            'WHERE id_reserva = :id_reserva',
            "id_reserva={$id}"
        );

        // Atualiza reserva
        $updateReserva = new Update();
        $updateReserva->ExeUpdate(
            'reservas',
            [
                'cancelada_rubens' => 'S',
                'fase_rubens' => 0
            ],
            'WHERE id = :id',
            "id={$id}"
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
            "SELECT r.*, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor FROM reservas AS r LEFT JOIN pagamentos AS p ON p.id_reserva = r.id WHERE r.id_motel = :id_motel AND r.processado = 'N' ORDER BY r.id DESC",
            "id_motel={$id_motel}"
        );
        return $read->getResult();
    }

}