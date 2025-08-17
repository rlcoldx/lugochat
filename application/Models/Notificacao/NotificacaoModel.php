<?php

namespace Agencia\Close\Models\Notificacao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class NotificacaoModel extends Model 
{
    public function getUsersID($offset = 0, $limit = 10000)
    {
        $read = new Read();
        $termos = "WHERE tipo = 4 AND pushKey is not null AND pushKey <> 'null' ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $parseString = "limit={$limit}&offset={$offset}";
        $read->exeRead("usuarios", $termos, $parseString);
        return $read;
    }
}