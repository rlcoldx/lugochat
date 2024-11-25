<?php

namespace Agencia\Close\Models\Bot;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Bot extends Model 
{

    public function getQuestions($company): Read
    {
    	$this->read = new Read();
        $this->read->FullRead("SELECT * FROM chat_bot WHERE id_motel = :id_motel AND `Status` = 'S' ORDER BY id ASC", "id_motel={$company}");
        return $this->read;
    }

    public function getQuestionsEdit($company): Read
    {
    	$this->read = new Read();

        if(!empty($_GET['q'])) {
            $filter = "AND (id = '".$_GET['q']."' OR parent = '".$_GET['q']."')";
        }else{
            $filter = "AND (primeira = 'S')";
        }

        $this->read->FullRead("SELECT * FROM chat_bot WHERE id_motel = :id_motel AND `Status` = 'S' $filter ORDER BY id ASC", "id_motel={$company}");
        return $this->read;
    }

    public function getOpcionais($company, $parent): Read
    {
        $this->read = new Read();

        if($parent != 0) {
            $filter = "AND parent <= (SELECT parent FROM chat_bot WHERE id = '".$parent."')";
        }else{
            $filter = "";
        }

        $this->read->FullRead("SELECT * FROM chat_bot WHERE id_motel = :id_motel AND `Status` = 'S' AND tipo = 'opcao' $filter ORDER BY parent ASC", "id_motel={$company}");
        return $this->read;
    }

    public function saveBemVindo($params, $company): Create
    {
        $data = [
            'id_motel' => $company,
            'texto' => $params['question'],
            'tipo' => 'questao',
            'parent' => '0',
            'primeira' => 'S'
        ];

        $create = new Create();
        $create->ExeCreate('chat_bot', $data);
        return $create;
    }

    public function editQuestion($params, $company): Update
    {
        //$data = ['cookie_key' => $params];
        $update = new Update();
        $update->ExeUpdate('chat_bot', $params, 'WHERE `id_motel` = :company AND `id` = :id', "company={$company}&id={$params['id']}");
        return $update;
    }

}