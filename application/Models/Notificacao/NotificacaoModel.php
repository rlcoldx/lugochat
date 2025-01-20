<?php

namespace Agencia\Close\Models\Notificacao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class NotificacaoModel extends Model 
{
    public function getUsersID()
    {
        $read = new Read();
        $read->exeRead('usuarios', 'WHERE tipo = 4 AND pushKey is not null ORDER BY id DESC');
        return $read;
    }
}