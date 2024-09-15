<?php

namespace Agencia\Close\Models\Widget;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Create;
use Agencia\Close\Models\Model;

class Widget extends Model 
{

    public function getEmpresaByCode($codigo): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `usuarios` WHERE codigo = :codigo AND `status` = 'Ativo' LIMIT 1", "codigo={$codigo}");
        return $this->read;
    }

    public function checkHistorico($id_empresa, $userID): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_historico` WHERE id_empresa = :id_empresa AND id_user = :userID ORDER BY `data` ASC", "id_empresa={$id_empresa}&userID={$userID}");
        return $this->read;
    }

    public function loadPrimeira($id_empresa): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_bot` WHERE id_empresa = :id_empresa AND `primeira` = 'S' AND `status` = 'S' ORDER BY `id` ASC", "id_empresa={$id_empresa}");
        return $this->read;
    }

    public function getPergunta($id_empresa, $id_pergunta): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_bot` WHERE id_empresa = :id_empresa AND (id = :id_pergunta OR parent = :id_pergunta) AND `status` = 'S' ORDER BY `parent` ASC", "id_empresa={$id_empresa}&id_pergunta={$id_pergunta}");
        return $this->read;
    }

    public function getResposta($id_empresa, $id_pergunta): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_bot` WHERE id_empresa = :id_empresa AND id = :id_pergunta  AND `status` = 'S' ORDER BY `parent` ASC", "id_empresa={$id_empresa}&id_pergunta={$id_pergunta}");
        return $this->read;
    }

    public function getProximaPergunta($id_empresa, $id_pergunta): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_bot` WHERE id_empresa = :id_empresa AND parent = :id_pergunta AND `status` = 'S' ORDER BY `parent` ASC LIMIT 1", "id_empresa={$id_empresa}&id_pergunta={$id_pergunta}");
        return $this->read;
    }

    public function getProximaResposta($id_empresa, $id_pergunta): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM `chat_bot` WHERE id_empresa = :id_empresa AND parent = :id_pergunta AND `status` = 'S' ORDER BY `parent` ASC", "id_empresa={$id_empresa}&id_pergunta={$id_pergunta}");
        return $this->read;
    }

    public function saveHistorico($params, $userID, $tipo = '')
    {
        if($tipo == 'resposta') {
            $params['tipo'] = 'resposta';
        }

        $params['id_user'] = $userID;
        $params['id_bot'] = $params['id'];

        unset($params['id']);
        unset($params['data']);
        $create = new Create();
        $create->ExeCreate('chat_historico', $params);
    }

}