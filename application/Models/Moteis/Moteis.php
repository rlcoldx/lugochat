<?php

namespace Agencia\Close\Models\Moteis;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Moteis extends Model
{

    public function getMoteis(): Read
    {
        $read = new Read();
        $read->ExeRead("usuarios", "WHERE tipo = '2' ORDER BY id DESC");
        return $read;
    }

    public function getMoteisList(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT id, nome FROM usuarios WHERE tipo = '2' ORDER BY nome ASC");
        return $read;
    }

    public function getMotel($id): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM usuarios WHERE tipo = '2' AND id = :id ORDER BY id DESC LIMIT 1", "id={$id}");
        return $read;
    }

    public function createDraft(array $params): Create
    {
        //SALVA O RASCUNHO
        $create = new Create();

        $params['tipo'] = '2';
        $params['cargo'] = 'Motel';
        $params['senha'] = sha1($params['senha']);
        $create->ExeCreate('usuarios', $params);
        return $create;
    }

    public function saveEdit(array $params): Update
    {
        //SALVA EDIÇÃO DO PRODUTO
        $update = new Update();
        $id = $params['id'];
        $params['empresa'] = $params['id'];

        if($params['senha'] != ''){
            $params['senha'] = sha1($params['senha']);
        }else{
            unset($params['senha']);
        }

        unset($params['id']);

        $update->ExeUpdate('usuarios', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }


    public function excluirMotel($id_motel, $status)
    {
        $read = new Read();
        $read->FullRead("UPDATE `usuarios` SET `status` = :status_item WHERE `id` = :id_motel", "id_motel={$id_motel}&status_item={$status}");
    }



}