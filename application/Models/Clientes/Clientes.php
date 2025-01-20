<?php

namespace Agencia\Close\Models\Clientes;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class Clientes extends Model 
{

    public function getClientes(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE tipo = '4' ORDER BY id DESC");
        return $read;
    }

}