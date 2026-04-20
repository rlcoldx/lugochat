<?php

namespace Agencia\Close\Models\Paginas;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Paginas extends Model
{

    public function getPaginas(): Read
    {
        $read = new Read();
        $read->ExeRead("paginas", " ORDER BY id DESC");
        return $read;
    }

    public function getPagina($id): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM paginas WHERE id = :id ORDER BY id DESC LIMIT 1", "id={$id}");
        return $read;
    }

    public function createDraft(array $params): Read
    {
        //SALVA O RASCUNHO
        $create = new Create();
        $create->ExeCreate('paginas', $params);
        //RETORNA O ITEM SALVO
        $read = new Read();
        $read->FullRead("SELECT * FROM paginas ORDER BY id DESC LIMIT 1");
        return $read;
    }

    public function saveEdit(array $params): Update
    {
        //SALVA EDIÇÃO DO PRODUTO
        $update = new Update();
        $id = $params['id'];
        unset($params['id']);

        $update->ExeUpdate('paginas', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }


    public function excluirPagina($id_pagina)
    {
        $read = new Read();
        $read->FullRead("DELETE * FROM `paginas` WHERE `id` = :id_pagina", "id_pagina={$id_pagina}");
    }

    public function getBanners(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM banners ORDER BY `order`,`id` DESC");
        return $read;
    }
}