<?php

namespace Agencia\Close\Models\Saques;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class SaquesAdminPainel extends Model
{
    public function getSaques(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT s.*, u.nome, u.email, c.`conta_banco`,c.`banco_pix`,c.`conta_ag`,c.`conta_numero`,c.`conta_tipo`,c.`conta_responsavel`,c.`conta_cpf_cnpj`
        FROM saques AS s 
        JOIN contas_bancarias AS c ON c.id = s.id_conta_bancaria
        JOIN usuarios AS u ON u.id = s.id_motel
        GROUP BY s.id ORDER BY s.id DESC");
        return $read;
    }

    public function statusSave($params): Read
    {
        $read = new Read();
        $read->FullRead("UPDATE `saques` SET `status` = :status WHERE `id` = :id", "id={$params['id']}&status={$params['status']}");
        return $read;
    }

}