<?php

namespace Agencia\Close\Models\Cupons;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class CuponsModel extends Model
{
    public function getCupons(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM cupons ORDER BY id DESC");
        return $read;
    }

    public function getCupomID($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM cupons WHERE id = :id ORDER BY id DESC", "id={$id}");
        return $read;
    }

    public function createCupom($data): Create
    {
        unset($data['id']);
        $create = new Create();
        $create->ExeCreate('cupons', $data);

        return $create;
    }

    public function updateCupom($data, $id): Update
    {
        $update = new Update();
        $update->ExeUpdate('cupons', $data, 'WHERE id = :id', "id={$id}");
        return $update;
    }

}