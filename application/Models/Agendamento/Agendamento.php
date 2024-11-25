<?php

namespace Agencia\Close\Models\Agendamento;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Agendamento extends Model 
{

    public function saveAgendamentos($params)
    {
        $codigo = $this->gerarCodigoAgendamento();

        $data = [
            'id_motel' =>$params['id_motel'],
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

    public function getAgendamentoEspera($codigo)
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM agendamentos WHERE codigo = :codigo", "codigo={$codigo}");
        return $read;
        
    }
    
}