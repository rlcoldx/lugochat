<?php

namespace Agencia\Close\Models\Saques;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class SaquesPainel extends Model
{
    public function getTotalReservas($id_licenciado): Read
    {
        $read = new Read();
        $read->FullRead("SELECT SUM(r.valor_reserva) AS total FROM reservas AS r
        JOIN pagamentos AS p ON p.id_reserva = r.id
        JOIN usuarios AS u ON u.id = r.id_restaurante
        WHERE u.licenciado = :id_licenciado 
        AND p.pagamento_status = 'approved'", "id_licenciado={$id_licenciado}");
        return $read;
    }

    public function getTotalSaques($id_licenciado): Read
    {
        $read = new Read();
        $read->FullRead("SELECT id_licenciado, SUM(valor) AS total FROM saques WHERE id_licenciado = :id_licenciado AND `status` <> 'Rejeitado'", "id_licenciado={$id_licenciado}");
        return $read;
    }

    public function getContas_Bancarias($id_licenciado): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM contas_bancarias WHERE id_licenciado = :id_licenciado ORDER BY id DESC", "id_licenciado={$id_licenciado}");
        return $read;
    }

    public function getContaID($id_licenciado, $id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM contas_bancarias WHERE id_licenciado = :id_licenciado AND id = :id", "id_licenciado={$id_licenciado}&id={$id}");
        return $read;
    }

    public function createConta($data, $id_licenciado): Create
    {
        $data['id_licenciado'] = $id_licenciado;
        unset($data['id']);
        $create = new Create();
        $create->ExeCreate('contas_bancarias', $data);

        return $create;
    }

    public function updateConta($data, $id_licenciado, $id): Update
    {
        $update = new Update();
        $update->ExeUpdate('contas_bancarias', $data, 'WHERE id = :id AND id_licenciado = :id_licenciado', "id={$id}&id_licenciado={$id_licenciado}");
        return $update;
    }

    public function getSaques($id_licenciado): Read
    {
        $read = new Read();
        $read->FullRead("SELECT s.*, c.`conta_banco`,c.`banco_pix`,c.`conta_ag`,c.`conta_numero`,c.`conta_tipo`,c.`conta_responsavel`,c.`conta_cpf_cnpj`
                        FROM saques AS s 
                        INNER JOIN contas_bancarias AS c ON c.id = s.id_conta_bancaria
                        WHERE s.id_licenciado = :id_licenciado GROUP BY s.id ORDER BY s.id DESC", "id_licenciado={$id_licenciado}");
        return $read;
    }

    public function getSaquesMes($id_licenciado): Read
    {
        $read = new Read();
        $read->FullRead("SELECT COUNT(*) AS total_saques
        FROM saques
        WHERE status IN ('Pendente', 'Concluido')
        AND id_licenciado = :id_licenciado
        AND YEAR(date_create) = YEAR(CURDATE())
        AND MONTH(date_create) = MONTH(CURDATE())", "id_licenciado={$id_licenciado}");
        return $read;
    }

    public function createSaque($data, $id_licenciado): Create
    {
        
        $data['id_licenciado'] = $id_licenciado;
        $data['valor'] = str_replace(',', '.', str_replace('.', '', $data['valor']));
        unset($data['id']);

        $create = new Create();
        $create->ExeCreate('saques', $data);
        return $create;
    }

}