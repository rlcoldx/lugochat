<?php

namespace Agencia\Close\Models\Moteis;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Categorias extends Model
{
    public function getCategory(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM categorias ORDER BY nome ASC");
        return $this->read;
    }

    public function getCategoriasID($id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM categorias WHERE id = :id", "id={$id} ORDER BY nome ASC");
        return $this->read;
    }

    public function getSemCategorias(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT p.* FROM usuarios AS p LEFT JOIN moteis_categorias AS pc ON pc.id_motel = p.id WHERE pc.id_motel IS NULL");
        return $this->read;
    }

    public function createCategory($params): Create
    {
        $create = new Create();
        unset($params['action']);

        if($params['parent'] != '0') {

            $this->read = new Read();
            $this->read->FullRead("SELECT * FROM categorias WHERE id = :id", "id={$params['parent']}");
            $parent = $this->read->getResult()[0];

            $params['nivel'] = ($parent['nivel'] + 1);

        }else{
            $params['nivel'] = 0;
            $params['parent'] = 0;
        }

        $create->ExeCreate('categorias', $params);
        return $create;
    }


    public function editarCategory($params): Update
    {
        $update = new Update();
        unset($params['action']);

        if($params['parent'] != '0') {

            $this->read = new Read();
            $this->read->FullRead("SELECT * FROM categorias WHERE id = :id", "id={$params['parent']}");
            $parent = $this->read->getResult()[0];

            $params['nivel'] = ($parent['nivel'] + 1);

        }else{

            $params['nivel'] = 0;
            $params['parent'] = 0;
            
        }

        $id = $params['id'];
        unset($params['id']);

        $update->ExeUpdate('categorias', $params, 'WHERE `id` = :id', "id={$id}");

        $update_moteis_categorias = new Update();
        $update_moteis_categorias->ExeUpdate('moteis_categorias', $params, 'WHERE `id_categoria` = :id', "id={$id}");

        $this->updateCategoriesProduct($id);

        return $update;
    }

    public function updateCategoriesProduct($id_categoria)
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM moteis_categorias WHERE id_categoria = :id_categoria", "id_categoria={$id_categoria}");
        $categoria_dados_lista_produtos = $this->read->getResult();
        
        foreach ($categoria_dados_lista_produtos as $categoria_lista_produtos){
            
            $categorias_ids = '';
            $categorias_nomes = '';
            
            /* PEGA TODOS AS CATEGORIAS DO PRODUTO DA CATEGORIA EDITADA PARA ATUALIZAR */
            $this->read->FullRead("SELECT * FROM moteis_categorias WHERE id_motel = :id_motel", "id_motel={$categoria_lista_produtos['id_motel']}");
            $categoria_dados_lista_produtos_select = $this->read->getResult();
            
            foreach ($categoria_dados_lista_produtos_select as $categoria_lista_produtos_select){
                $categorias_ids .= $categoria_lista_produtos_select['id_categoria'].',';
                $categorias_nomes .= $categoria_lista_produtos_select['nome'].',';
            }

            $categorias_ids = substr($categorias_ids,0,-1);
            $categorias_nomes = substr($categorias_nomes,0,-1);

            $this->read->FullRead("UPDATE usuarios SET categoria_id = :categoria_id, categoria = :categoria WHERE id = :id_motel", "categoria_id={$categorias_ids}&categoria={$categorias_nomes}&id_motel={$categoria_lista_produtos['id_motel']}");

        }
    }
}