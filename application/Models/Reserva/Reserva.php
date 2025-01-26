<?php

namespace Agencia\Close\Models\Reserva;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Reserva extends Model 
{

    public function byCompany($coluna = '') {
        $empresa = '';
        if($_SESSION['busca_perfil_tipo'] != '0'){
            $empresa = " AND $coluna = '".$_SESSION['busca_perfil_empresa']."' ";
        }
        return $empresa;
    }

    public function checkReservas(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM reservas WHERE status_reserva = 'Pendente' ".$this->byCompany('id_motel')." ORDER BY id DESC");
        return $this->read;
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