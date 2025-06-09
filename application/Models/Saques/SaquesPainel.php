<?php

namespace Agencia\Close\Models\Saques;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class SaquesPainel extends Model
{
    public function getTotalReservas($id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT SUM(r.valor_reserva) AS total FROM reservas AS r
        JOIN pagamentos AS p ON p.id_reserva = r.id
        JOIN usuarios AS u ON u.id = r.id_motel
        WHERE u.id = :id_motel 
        AND p.pagamento_status = 'approved'", "id_motel={$id_motel}");
        return $read;
    }

    public function getTotalSaques($id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT id_motel, SUM(valor) AS total FROM saques WHERE id_motel = :id_motel AND `status` <> 'Rejeitado'", "id_motel={$id_motel}");
        return $read;
    }

    public function getContas_Bancarias($id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM contas_bancarias WHERE id_motel = :id_motel ORDER BY id DESC", "id_motel={$id_motel}");
        return $read;
    }

    public function getContaID($id_motel, $id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT * FROM contas_bancarias WHERE id_motel = :id_motel AND id = :id", "id_motel={$id_motel}&id={$id}");
        return $read;
    }

    public function createConta($data, $id_motel): Create
    {
        $data['id_motel'] = $id_motel;
        unset($data['id']);

        $data['conta_cpf_cnpj'] = str_replace('.', '', $data['conta_cpf_cnpj']);
        $data['conta_cpf_cnpj'] = str_replace('-', '', $data['conta_cpf_cnpj']);
        $data['conta_cpf_cnpj'] = str_replace('/', '', $data['conta_cpf_cnpj']);

        $create = new Create();
        $create->ExeCreate('contas_bancarias', $data);

        return $create;
    }

    public function updateConta($data, $id_motel, $id): Update
    {
        $data['conta_cpf_cnpj'] = str_replace('.', '', $data['conta_cpf_cnpj']);
        $data['conta_cpf_cnpj'] = str_replace('-', '', $data['conta_cpf_cnpj']);
        $data['conta_cpf_cnpj'] = str_replace('/', '', $data['conta_cpf_cnpj']);

        $update = new Update();
        $update->ExeUpdate('contas_bancarias', $data, 'WHERE id = :id AND id_motel = :id_motel', "id={$id}&id_motel={$id_motel}");
        return $update;
    }

    public function getSaques($id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT s.*, c.`conta_banco`,c.`banco_pix`,c.`conta_ag`,c.`conta_numero`,c.`conta_tipo`,c.`conta_responsavel`,c.`conta_cpf_cnpj`
                        FROM saques AS s 
                        INNER JOIN contas_bancarias AS c ON c.id = s.id_conta_bancaria
                        WHERE s.id_motel = :id_motel GROUP BY s.id ORDER BY s.id DESC", "id_motel={$id_motel}");
        return $read;
    }

    public function getSaquesMes($id_motel): Read
    {
        $read = new Read();
        $read->FullRead("SELECT COUNT(*) AS total_saques
        FROM saques
        WHERE status IN ('Pendente', 'Concluido')
        AND id_motel = :id_motel
        AND YEAR(date_create) = YEAR(CURDATE())
        AND MONTH(date_create) = MONTH(CURDATE())", "id_motel={$id_motel}");
        return $read;
    }

    public function createSaque($data, $id_motel): Create
    {
        $data['id_motel'] = $id_motel;
        $data['valor'] = str_replace(',', '.', str_replace('.', '', $data['valor']));
        unset($data['id']);

        $create = new Create();
        $create->ExeCreate('saques', $data);
        return $create;
    }
}