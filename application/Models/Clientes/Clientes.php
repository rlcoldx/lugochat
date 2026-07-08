<?php

namespace Agencia\Close\Models\Clientes;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Clientes extends Model 
{

    public function getClientes(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT u.*,
            (SELECT COUNT(*) FROM usuarios_banidos b WHERE b.id_usuario = u.id AND b.status = 'ativo') AS banido
            FROM usuarios AS u WHERE u.tipo = '1' ORDER BY u.id DESC");
        return $read;
    }

    public function getClientesByCompany(): Read
    {
        $read = new Read();
        $read->FullRead("SELECT u.*,
            (SELECT COUNT(*) FROM usuarios_banidos b WHERE b.id_usuario = u.id AND b.status = 'ativo') AS banido
            FROM usuarios AS u
            INNER JOIN reservas AS r ON r.id_usuario = u.id
            WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." GROUP BY u.id ORDER BY u.id DESC");
        return $read;
    }

    public function getUsuarioById($id): Read
    {
        $read = new Read();
        $read->FullRead("SELECT id, nome, email, telefone, cpf, ip FROM usuarios WHERE id = :id AND tipo = '1' LIMIT 1", "id={$id}");
        return $read;
    }

    public function banimentoAtivo($idUsuario): Read
    {
        $read = new Read();
        $read->FullRead("SELECT id FROM usuarios_banidos WHERE id_usuario = :id AND status = 'ativo' LIMIT 1", "id={$idUsuario}");
        return $read;
    }

    /**
     * Registra um novo banimento e semeia o IP conhecido do cliente (se houver).
     * @return int|null ID do banimento criado
     */
    public function banirCliente($idUsuario, array $usuario, $banidoPor, string $motivo = '')
    {
        $create = new Create();
        $create->ExeCreate('usuarios_banidos', [
            'id_usuario' => (int) $idUsuario,
            'nome'       => $usuario['nome'] ?? '',
            'email'      => isset($usuario['email']) ? strtolower(trim($usuario['email'])) : '',
            'telefone'   => \normalizarTelefone($usuario['telefone'] ?? ''),
            'cpf'        => preg_replace('/\D/', '', (string) ($usuario['cpf'] ?? '')),
            'motivo'     => $motivo,
            'banido_por' => $banidoPor ? (int) $banidoPor : null,
            'status'     => 'ativo',
        ]);

        $idBanido = $create->getResult();

        if ($idBanido && !empty($usuario['ip'])) {
            $ip = new Create();
            $ip->ExeCreate('usuarios_banidos_ips', [
                'id_banido' => (int) $idBanido,
                'ip'        => $usuario['ip'],
            ]);
        }

        return $idBanido;
    }

    public function desbanirCliente($idUsuario): Update
    {
        $update = new Update();
        $update->ExeUpdate(
            'usuarios_banidos',
            ['status' => 'removido'],
            'WHERE id_usuario = :id_usuario AND status = :stat',
            "id_usuario={$idUsuario}&stat=ativo"
        );
        return $update;
    }

}