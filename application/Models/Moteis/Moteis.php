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

        unset($params['possuem']);
        unset($params['categories_id']);

        $create->ExeCreate('usuarios', $params);
        return $create;
    }

    public function saveEdit(array $params): Update
    {
        //SALVA EDIÇÃO DO PRODUTO
        $update = new Update();
        $id = $params['id'];
        $params['empresa'] = $params['id'];

        if(isset($params['categories_id'])){
            $dados_categorias = $this->saveCategory($id, $params['categories_id']);
            $params['categoria_id'] = $dados_categorias[0];
            $params['categoria'] = $dados_categorias[1];
        }
        
        $possuem = '';
        if (isset($params['possuem'])) {
            $possuem = implode(',', $params['possuem']);
        }
        unset($params['possuem']);
        $params['possuem'] = $possuem;

        if($params['senha'] != ''){
            $params['senha'] = sha1($params['senha']);
        }else{
            unset($params['senha']);
        }

        unset($params['id']);
        unset($params['categories_id']);

        $update->ExeUpdate('usuarios', $params, 'WHERE `id` = :id', "id={$id}");
        return $update;
    }


    public function excluirMotel($id_motel, $status)
    {
        $read = new Read();
        $read->FullRead("UPDATE `usuarios` SET `status` = :status_item WHERE `id` = :id_motel", "id_motel={$id_motel}&status_item={$status}");
    }

    public function saveCategory($id_motel, $categorias)
    {
        $read = new Read();
        $read->FullRead("DELETE FROM moteis_categorias WHERE `id_motel` = :id_motel", "id_motel={$id_motel}");

        $categorias_ids = '';
		$categorias_nomes = '';

		if(is_countable($categorias)){
			for ($i=0; $i <count($categorias); $i++) {

                $read = new Read();
                $read->FullRead("SELECT * FROM categorias WHERE `id` = :id LIMIT 1", "id={$categorias[$i]}");
                $categoria = $read->getResult()[0];

                $cat_insert = array('id_motel' => $id_motel, 'id_categoria' => $categoria['id'], 'nome' => $categoria['nome'], 'slug' => $categoria['slug'], 'nivel' => $categoria['nivel'], 'parent' => $categoria['parent']);
                $create = new Create();
                $create->ExeCreate('moteis_categorias', $cat_insert);

				$categorias_ids .= $categoria['id'].',';
				$categorias_nomes .= $categoria['nome'].',';

			}
		}

		$categorias_ids = substr($categorias_ids,0,-1);
		$categorias_nomes = substr($categorias_nomes,0,-1);

        return [$categorias_ids, $categorias_nomes];
		/*****/
    }

}