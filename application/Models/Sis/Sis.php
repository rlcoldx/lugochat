<?php

namespace Agencia\Close\Models\Sis;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Sis extends Model 
{

    public function getMotelSis(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE tipo = 2 AND integracao = 'sis' AND token <> '' AND `status` = 'Ativo'");
        return $read;
    }

    public function getMotelSisSingle($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE id = :id AND tipo = 2 AND integracao = 'sis' AND token <> '' AND `status` = 'Ativo'", "id={$id}");
        return $read;
    }

    public function updateDisponibilidade($id_motel, $params)
    {
        $update = new Update();
        $dados['quantidade'] = $params['total'];
        $dados['disponibilidade'] = $params['free'];
        $update->ExeUpdate('suites', $dados, 'WHERE `id_motel` = :id_motel AND sis_suite = :sis_suite', "id_motel={$id_motel}&sis_suite={$params['id']}");
        return $update;
    }

}