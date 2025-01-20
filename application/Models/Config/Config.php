<?php

namespace Agencia\Close\Models\Config;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;


class Config extends Model 
{

    public function getConfig(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM configuracoes WHERE id = '1'");
        return $this->read;
    }

    public function getConfigSave($params): Update
    {
        $id = 1;
        $update = new Update();
        $update->ExeUpdate('configuracoes', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }
    
}