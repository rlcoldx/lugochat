<?php

namespace Agencia\Close\Models\Suites;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Suites extends Model 
{

	public function getSuites($id_motel): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT suites.*, sp.valor FROM suites
                        LEFT JOIN ( SELECT id_suite, MIN(valor) AS valor FROM suites_precos GROUP BY id_suite) AS sp ON suites.id = sp.id_suite
                        WHERE suites.id_motel = :id_motel AND suites.`status` <> 'Deletado' ORDER BY suites.id DESC;", "id_motel={$id_motel}");
        return $read;
    }

    public function getSuite($id, $id_motel): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites WHERE id = :id AND id_motel = :id_motel ORDER BY id DESC LIMIT 1", "id={$id}&id_motel={$id_motel}");
        return $read;
    }

    public function getSuitePrecos($id_suite, $id_motel): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites_precos WHERE id_suite = :id_suite AND id_motel = :id_motel AND `status` = 'S' ORDER BY `ordem`, `id` ASC", "id_suite={$id_suite}&id_motel={$id_motel}");
        return $read;
    }

    public function createDraft(array $params, $id_motel): Read
    {
        //SALVA O RASCUNHO
        $create = new Create();
        $params['id_motel'] = $id_motel;
        $params['status'] = 'Rascunho';
        $create->ExeCreate('suites', $params);
        //RETORNA O ITEM SALVO
        $read = new Read();
        $read->FullRead("SELECT * FROM suites WHERE id_motel = :id_motel ORDER BY id DESC LIMIT 1", "id_motel={$id_motel}");
        return $read;
    }

    public function saveEdit(array $params, $id_motel): Update
    {
        //SALVA EDIÇÃO DA SUITE
        $update = new Update();
        $id = $params['id'];

        unset($params['id']);
        unset($params['suiteid']);
        unset($params['fileuploader-list-files']);
        unset($params['files']);
        unset($params['preco']);
        unset($params['price_chance']);
        unset($params['price_chance']);

        if(($params['promocao'] != '') && ($params['promocao'] != '0,00') ){
            $params['promocao'] = $this->converterValoes($params['promocao']);
        }else{
            $params['promocao'] = '';
        }

        $update->ExeUpdate('suites', $params, 'WHERE `id_motel` = :id_motel AND `id` = :id', "id_motel={$id_motel}&id={$id}");
        return $update;
    }


    public function saveEditPrecos(array $precos, $id_suite, $id_motel)
    {
        $read = new Read();
        $read->FullRead("UPDATE `suites_precos` SET `status` = 'N' WHERE  `id_motel` = :id_motel AND `id_suite` = :id_suite", "id_suite={$id_suite}&id_motel={$id_motel}");

        foreach ($precos as $preco)
        {
            $dias = implode(',', $preco['dias']);
            $preco['id_motel'] = $id_motel;
            $preco['id_suite'] = $id_suite;
            $preco['dias'] = $dias;
            $preco['valor'] = $this->converterValoes($preco['valor']);
            
            $create = new Create();
            $create->ExeCreate('suites_precos', $preco);
        }
    }

    public function converterValoes($val){
        $valorBR = str_replace('.', '', $val);
        $valorBR = str_replace(',', '.', $valorBR);
        $valorDecimal = floatval($valorBR);
        $valorFormatado = number_format($valorDecimal, 2, '.', '');
        return $valorFormatado;
    }

    public function getSuiteImages($id_suite, $id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM suites_imagens WHERE id_suite = :id_suite AND id_motel = :id_motel ORDER BY `order`,`id` DESC", "id_suite={$id_suite}&id_motel={$id_motel}");
        return $read;
    }

    public function excluirSuite($id_suite, $id_motel)
    {
        $read = new Read();
        $read->FullRead("UPDATE `suites` SET `status` = 'Deletado' WHERE  `id_motel` = :id_motel AND `id` = :id_suite", "id_suite={$id_suite}&id_motel={$id_motel}");
    }

    public function duplicarSuite($id)
    {
        $suite = new Read();
        $suite->FullRead("SELECT * FROM suites WHERE id = :id ORDER BY id DESC LIMIT 1", "id={$id}");
        // CLONA E SALVA
        $id_clone = $this->duplicarSuiteBase($suite->getResult()[0])->getResult();

        $suite_precos = new Read();
        $suite_precos->FullRead("SELECT * FROM suites_precos WHERE id_suite = :id_suite AND `status` = 'S' ORDER BY `periodo` ASC", "id_suite={$id}");

        $this->duplicarSuitePrecosBase($suite_precos->getResult(), $id_clone);

        echo '1';
    }

    public function duplicarSuiteBase($params): Create
    {
        $create = new Create();
        unset($params['id']);
        $params['status'] = 'Rascunho';
        $create->ExeCreate('suites', $params);
        return $create;
    }

    public function duplicarSuitePrecosBase($params, $id_clone)
    {
        foreach ($params as $suite) {
            unset($suite['id']);
            $suite['id_suite'] = $id_clone;
            $create = new Create();
            $create->ExeCreate('suites_precos', $suite);
        }        
    }

    public function getCardadio(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM cardapios ORDER BY `order`,`id` DESC");
        return $read;
    }
    
}