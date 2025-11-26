<?php

namespace Agencia\Close\Models\Reserva;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;
use Agencia\Close\Models\Sis\Sis;

class Reserva extends Model 
{
    public function checkReservas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM reservas WHERE status_reserva = 'Pendente' ".$this->byCompany('id_motel')." ORDER BY id DESC");
        return $this->read;
    }

    public function checkReservasExpiradas(): array
    {
        // Busca reservas que não estão recusadas/canceladas, pagamento não aprovado e passaram de 10 minutos
        $this->read = new Read();
        $this->read->FullRead("SELECT r.*, p.pagamento_status 
            FROM reservas AS r
            LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
            WHERE r.status_reserva NOT IN ('Recusado', 'Cancelado')
            AND (p.pagamento_status IS NULL OR p.pagamento_status <> 'approved')
            AND r.date_create < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
            ORDER BY r.date_create ASC");
        
        $reservasExpiradas = $this->read->getResult();
        $reservasCanceladas = [];
        
        if ($reservasExpiradas) {
            foreach ($reservasExpiradas as $reserva) {
                // Atualiza o status para Cancelado
                $update = new Update();
                $update->ExeUpdate('reservas', ['status_reserva' => 'Cancelado'], 'WHERE id = :id',  "id={$reserva['id']}");

                if ($reserva['integracao'] == 'api') {
                    $update->ExeUpdate('reservas', ['fase_api' => 0, 'processado_api' => 'N', 'cancelada_api' => 'S'], 'WHERE id = :id',  "id={$reserva['id']}");
                }

                if ($reserva['integracao'] == 'sis') {
                    $moteis = new Sis;
                    $sis = $moteis->getMotelSisSingle($reserva['id_motel'])->getResultSingle();
                    $this->getCancelarReservaSis($reserva['id_reserva_sis'], $sis['token']);
                    $update->ExeUpdate('reservas', ['status_sis' => 8], 'WHERE id = :id',  "id={$reserva['id']}");
                }

            }
        }
        
        return $reservasCanceladas;
    }

    public function getCancelarReservaSis($id_reserva_sis, $token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => SIS_API . '/api/reservation/' . $id_reserva_sis, // Endpoint da reserva no SIS
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE', // Método DELETE para cancelar a reserva
            CURLOPT_HTTPHEADER => array(
                'token: ' . $token,        // Token do motel
                'softhouse: ' . SOFTHOUSE  // Identificação da softhouse, definida no config
            ),
        ));

        $response = curl_exec($curl); // Executa a requisição
        curl_close($curl);            // Fecha a conexão cURL
    }


    public function getReservas($limit = 99999): Read
    {
        $read = new Read();

        $read->FullRead("SELECT r.*, s.nome AS suite_nome, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor FROM reservas AS r
        INNER JOIN suites AS s ON s.id = r.id_suite
        LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
        WHERE status_reserva <> '' ".$this->byCompany('r.id_motel')."
        ORDER BY r.id DESC LIMIT $limit");
        return $read;
    }

    public function getReservasWithFilters($filters = [], $limit = 20, $offset = 0): Read
    {
        $read = new Read();
        
        // Construção da query base
        $sql = "SELECT r.*, s.nome AS suite_nome, u.nome AS motel_nome, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor 
                FROM reservas AS r
                INNER JOIN suites AS s ON s.id = r.id_suite
                INNER JOIN usuarios AS u ON u.id = r.id_motel
                LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
                WHERE r.status_reserva <> ''";
        
        $params = [];
        
        // Filtro por status de pagamento
        if (!empty($filters['pagamento_status'])) {
            $sql .= " AND p.pagamento_status = :pagamento_status";
            $params[] = "pagamento_status={$filters['pagamento_status']}";
        }
        
        // Filtro por motel
        if (!empty($filters['id_motel'])) {
            $sql .= " AND r.id_motel = :id_motel";
            $params[] = "id_motel={$filters['id_motel']}";
        }
        
        // Busca por código ou nome
        if (!empty($filters['search'])) {
            $sql .= " AND (r.codigo LIKE :search OR r.nome LIKE :search)";
            $search = "%{$filters['search']}%";
            $params[] = "search={$search}";
        }
        
        // Ordenação
        if (!empty($filters['order'])) {
            switch ($filters['order']) {
                case 'proxima':
                    $sql .= " ORDER BY ABS(DATEDIFF(r.data_reserva, CURDATE())) ASC, r.data_reserva ASC";
                    break;
                case 'recente':
                default:
                    $sql .= " ORDER BY r.id DESC";
                    break;
            }
        } else {
            $sql .= " ORDER BY r.id DESC"; // Padrão: mais recente
        }
        
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
        
        $read->FullRead($sql, implode('&', $params));
        return $read;
    }

    public function countReservasWithFilters($filters = []): int
    {
        $read = new Read();
        
        $sql = "SELECT COUNT(*) as total 
                FROM reservas AS r
                INNER JOIN suites AS s ON s.id = r.id_suite
                LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
                WHERE r.status_reserva <> ''";
        
        $params = [];
        
        // Filtro por status de pagamento
        if (!empty($filters['pagamento_status'])) {
            $sql .= " AND p.pagamento_status = :pagamento_status";
            $params[] = "pagamento_status={$filters['pagamento_status']}";
        }
        
        // Filtro por motel
        if (!empty($filters['id_motel'])) {
            $sql .= " AND r.id_motel = :id_motel";
            $params[] = "id_motel={$filters['id_motel']}";
        }
        
        // Busca por código ou nome
        if (!empty($filters['search'])) {
            $sql .= " AND (r.codigo LIKE :search OR r.nome LIKE :search)";
            $search = "%{$filters['search']}%";
            $params[] = "search={$search}";
        }
        
        $read->FullRead($sql, implode('&', $params));
        $result = $read->getResultSingle();
        
        return $result ? (int)$result['total'] : 0;
    }

    public function statusReserva($id_reserva): Read
    {
        $read = new Read();
        $read->FullRead("SELECT r.*, s.nome AS suite_nome, p.pagamento_status, p.pagamento_metodo, p.pagamento_valor, p.external_reference FROM reservas AS r
        INNER JOIN suites AS s ON s.id = r.id_suite
        LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
        WHERE r.id = :id_reserva
        ORDER BY r.id DESC", "id_reserva={$id_reserva}");
        return $read;
    }
    
    public function statusReservaSave($params): Update
    {
        $id = $params['id'];
        unset($params['id']);
        $update = new Update();
        $update->ExeUpdate('reservas', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }
    
    
}