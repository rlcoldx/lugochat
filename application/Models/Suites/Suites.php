<?php

namespace Agencia\Close\Models\Suites;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Suites extends Model 
{

	public function getSuites($id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT suites.*, sp.valor FROM suites
                        LEFT JOIN ( SELECT id_suite, MIN(valor) AS valor FROM suites_precos GROUP BY id_suite) AS sp ON suites.id = sp.id_suite
                        WHERE suites.id_empresa = :id_empresa AND suites.`status` <> 'Deletado' ORDER BY suites.id DESC;", "id_empresa={$id_empresa}");
        return $read;
    }

    public function getSuite($id, $id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites WHERE id = :id AND id_empresa = :id_empresa ORDER BY id DESC LIMIT 1", "id={$id}&id_empresa={$id_empresa}");
        return $read;
    }

    public function getSuitePrecos($id_suite, $id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites_precos WHERE id_suite = :id_suite AND id_empresa = :id_empresa AND `status` = 'S' ORDER BY `ordem`, `id` ASC", "id_suite={$id_suite}&id_empresa={$id_empresa}");
        return $read;
    }

    public function createDraft(array $params, $id_empresa): Read
    {
        //SALVA O RASCUNHO
        $create = new Create();
        $params['id_empresa'] = $id_empresa;
        $params['status'] = 'Rascunho';
        $create->ExeCreate('suites', $params);
        //RETORNA O ITEM SALVO
        $read = new Read();
        $read->FullRead("SELECT * FROM suites WHERE id_empresa = :id_empresa ORDER BY id DESC LIMIT 1", "id_empresa={$id_empresa}");
        return $read;
    }

    public function saveEdit(array $params, $id_empresa): Update
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

        $update->ExeUpdate('suites', $params, 'WHERE `id_empresa` = :id_empresa AND `id` = :id', "id_empresa={$id_empresa}&id={$id}");
        return $update;
    }


    public function saveEditPrecos(array $precos, $id_suite, $id_empresa)
    {
        $read = new Read();
        $read->FullRead("UPDATE `suites_precos` SET `status` = 'N' WHERE  `id_empresa` = :id_empresa AND `id_suite` = :id_suite", "id_suite={$id_suite}&id_empresa={$id_empresa}");

        foreach ($precos as $preco)
        {
            $dias = implode(',', $preco['dias']);
            $preco['id_empresa'] = $id_empresa;
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

    public function getSuiteImages($id_suite, $id_empresa): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM suites_imagens WHERE id_suite = :id_suite AND id_empresa = :id_empresa ORDER BY `order`,`id` DESC", "id_suite={$id_suite}&id_empresa={$id_empresa}");
        return $read;
    }

    public function excluirSuite($id_suite, $id_empresa)
    {
        $read = new Read();
        $read->FullRead("UPDATE `suites` SET `status` = 'Deletado' WHERE  `id_empresa` = :id_empresa AND `id` = :id_suite", "id_suite={$id_suite}&id_empresa={$id_empresa}");
    }
    
}