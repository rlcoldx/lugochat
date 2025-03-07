<?php

namespace Agencia\Close\Models\Rubens;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Rubens extends Model 
{
    public function getSuites(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("WITH usuarios_cte AS (
        SELECT ROW_NUMBER() OVER (ORDER BY u.id) - 1 AS u_idx,
                u.id,
                u.nome,
                u.status
        FROM usuarios u
        WHERE u.integracao = 'rubens'
            AND EXISTS (SELECT 1 FROM suites WHERE id_motel = u.id)
        ),
        suites_cte AS (
        SELECT ROW_NUMBER() OVER (PARTITION BY s.id_motel ORDER BY s.id) - 1 AS s_idx,
                s.id_motel,
                s.id,
                s.nome,
                s.status
        FROM suites s
        )
        SELECT JSON_OBJECTAGG(u_idx, user_json) AS json_result
        FROM (
        SELECT u.u_idx,
                JSON_OBJECT(
                'ID', u.id,
                'nome', u.nome,
                'status', CASE WHEN u.status = 'Ativo' THEN 'S' ELSE 'N' END,
                'suites', (
                    SELECT JSON_OBJECTAGG(s.s_idx, JSON_OBJECT(
                                'ID', s.id,
                                'nome', s.nome,
                                'status', CASE WHEN s.status = 'Publicado' THEN 'S' ELSE 'N' END
                                ))
                    FROM suites_cte s
                    WHERE s.id_motel = u.id ORDER BY s.id ASC
                )
                ) AS user_json
        FROM usuarios_cte u
        ) t");
        return $this->read;
    }

    public function updateDisponibilidade($params)
    {
        $update = new Update();
        $dados['disponibilidade'] = $params['qtde'];
        $update->ExeUpdate('suites', $dados, 'WHERE `id` = :id AND `id_motel` = :id_motel', "id={$params['suite']}&id_motel={$params['motel']}");
        return $update;
    }

    public function getDisponibilidade($params): Read
    {   
        $bysuite = "";
        if(!empty($params['suite'])){
            $bysuite = " AND s.id = '".$params['suite']."' ";
        }
        $this->read = new Read();
        $this->read->FullRead("SELECT JSON_OBJECTAGG(s_idx, suite_json) AS json_result
        FROM (
        SELECT ROW_NUMBER() OVER (ORDER BY s.id) - 1 AS s_idx, JSON_OBJECT('ID', s.id, 'disponibilidade', s.disponibilidade) AS suite_json
        FROM suites s WHERE s.id_motel = :motel $bysuite ) t", "motel={$params['motel']}");
        return $this->read;
    }

}