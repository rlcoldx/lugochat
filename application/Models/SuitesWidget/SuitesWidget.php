<?php

namespace Agencia\Close\Models\SuitesWidget;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class SuitesWidget extends Model 
{

	public function getSuites($id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT suites.*, sp.valor, img.imagem FROM suites
                        LEFT JOIN ( SELECT id_suite, MIN(valor) AS valor FROM suites_precos WHERE `status` = 'S' GROUP BY id_suite) AS sp ON suites.id = sp.id_suite
                        LEFT JOIN ( SELECT id_suite,imagem,imagem_original,`order` FROM suites_imagens WHERE `order` = 0 GROUP BY id_suite ORDER BY `order` ASC) img ON suites.id = img.id_suite
                        WHERE suites.id_empresa = :id_empresa AND suites.`status` <> 'Deletado' ORDER BY suites.id DESC", "id_empresa={$id_empresa}");
        return $read;
    }

    public function getSuite($id, $id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites WHERE id = :id AND id_empresa = :id_empresa ORDER BY id DESC LIMIT 1", "id={$id}&id_empresa={$id_empresa}");
        return $read;
    }

    public function getSuitePrecosAll($id_suite, $id_empresa): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites_precos WHERE id_suite = :id_suite AND id_empresa = :id_empresa AND `status` = 'S' ORDER BY `ordem`, `id` ASC", "id_suite={$id_suite}&id_empresa={$id_empresa}");
        return $read;
    }

    public function getSuitePrecos($id_suite, $id_empresa, $diaDaSemana): Read
    {
    	$read = new Read();
        $read->FullRead("SELECT * FROM suites_precos WHERE id_suite = :id_suite AND id_empresa = :id_empresa AND dias LIKE '%".$diaDaSemana."%' AND `status` = 'S' ORDER BY `ordem`, `id` ASC", "id_suite={$id_suite}&id_empresa={$id_empresa}");
        return $read;
    }

    public function converterValoes($val){
        $valorBR = $val;
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

    public function saveAgendamentos($params)
    {
        $codigo = $this->gerarCodigoAgendamento();

        $data = [
            'id_empresa' =>$params['id_empresa'],
            'user_email' => $params['email'],
            'id_suite' => $params['id_suite'],
            'codigo' => $codigo,
            'valor_agendamento' => $params['select_valor'],
            'data_agendamento' => $params['agendamento_data'],
            'chegada_agendamento' => $params['horario_chegada'],
            'periodo_agendamento' => $params['agendamento_periodo']
        ];

        $create = new Create();
        $create->ExeCreate('agendamentos', $data);
        if($create) {
            $read = new Read();
            $read->FullRead("SELECT * FROM agendamentos WHERE codigo = :codigo", "codigo={$codigo}");
            return $read;
        }
    }

    public function gerarCodigoAgendamento()
	{

		$letras = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$codigo = '';
		
		for ($i = 0; $i < 8; $i++) {
			$codigo .= $letras[rand(0, strlen($letras) - 1)];
		}
		
		return $codigo;
	}
    
}