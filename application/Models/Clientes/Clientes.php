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
        $read->FullRead("SELECT * FROM usuarios WHERE tipo = '1' ORDER BY id DESC");
        return $read;
    }

    public function getClientesByCompany(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT u.* FROM usuarios AS u
        INNER JOIN reservas AS r ON r.id_usuario = u.id
        WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." ORDER BY u.id DESC");
        return $read;
    }

}