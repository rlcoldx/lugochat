<?php

namespace Agencia\Close\Models\Cupons;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class CuponsModel extends Model
{
    public function getCupons($id_motel = null): Read
    {
        $read = new Read();
        if($id_motel) {
            $read->FullRead("SELECT * FROM cupons WHERE id_motel = :id_motel ORDER BY id DESC", "id_motel={$id_motel}");
        } else {
            $read->FullRead("SELECT c.*, u.nome as nome_motel FROM cupons as c LEFT JOIN usuarios as u ON c.id_motel = u.id ORDER BY c.id DESC");
        }
        return $read;
    }

    public function getCupomID($id, $id_motel = null): Read
    {
        $read = new Read();
        if($id_motel) {
            $read->FullRead("SELECT * FROM cupons WHERE id = :id AND id_motel = :id_motel ORDER BY id DESC", "id={$id}&id_motel={$id_motel}");
        } else {
            $read->FullRead("SELECT * FROM cupons WHERE id = :id ORDER BY id DESC", "id={$id}");
        }
        return $read;
    }

    public function createCupom($data, $id_motel = null): Create
    {
        unset($data['id']);
        if($id_motel) {
            $data['id_motel'] = $id_motel;
        }
        $create = new Create();
        $create->ExeCreate('cupons', $data);

        return $create;
    }

    public function updateCupom($data, $id, $id_motel = null): Update
    {
        $update = new Update();
        if($id_motel) {
            $update->ExeUpdate('cupons', $data, 'WHERE id = :id AND id_motel = :id_motel', "id={$id}&id_motel={$id_motel}");
        } else {
            $update->ExeUpdate('cupons', $data, 'WHERE id = :id', "id={$id}");
        }
        return $update;
    }
}