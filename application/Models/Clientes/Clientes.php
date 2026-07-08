<?php

namespace Agencia\Close\Models\Clientes;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Clientes extends Model 
{

    /**
     * Monta o filtro de busca por nome, e-mail, CPF ou telefone.
     * @param array $params Recebe (por referência) os placeholders no formato do FullRead.
     */
    private function filtroBusca(string $busca, array &$params): string
    {
        $busca = trim($busca);
        if ($busca === '') {
            return '';
        }
        // FullRead usa parse_str (espera query string codificada): urlencode evita que
        // trechos como "%da" sejam interpretados como sequência hex e corrompam o termo.
        $params[] = 'busca=' . urlencode('%' . $busca . '%');
        return " AND (u.nome LIKE :busca OR u.email LIKE :busca OR u.cpf LIKE :busca OR u.telefone LIKE :busca) ";
    }

    public function getClientes(int $limit = 25, int $offset = 0, string $busca = ''): Read
    {
        $params = [];
        $where = $this->filtroBusca($busca, $params);

        $read = new Read();
        $read->FullRead("SELECT u.id, u.nome, u.email, u.cpf, u.telefone, u.data,
            (SELECT COUNT(*) FROM usuarios_banidos b WHERE b.id_usuario = u.id AND b.status = 'ativo') AS banido
            FROM usuarios AS u
            WHERE u.tipo = '1' {$where}
            ORDER BY u.id DESC LIMIT {$limit} OFFSET {$offset}", implode('&', $params));
        return $read;
    }

    public function contarClientes(string $busca = ''): int
    {
        $params = [];
        $where = $this->filtroBusca($busca, $params);

        $read = new Read();
        $read->FullRead("SELECT COUNT(*) AS total FROM usuarios AS u
            WHERE u.tipo = '1' {$where}", implode('&', $params));
        return (int) ($read->getResultSingle()['total'] ?? 0);
    }

    public function getClientesByCompany(int $limit = 25, int $offset = 0, string $busca = ''): Read
    {
        $params = [];
        $where = $this->filtroBusca($busca, $params);

        $read = new Read();
        $read->FullRead("SELECT u.id, u.nome, u.email, u.cpf, u.telefone, u.data,
            (SELECT COUNT(*) FROM usuarios_banidos b WHERE b.id_usuario = u.id AND b.status = 'ativo') AS banido
            FROM usuarios AS u
            INNER JOIN reservas AS r ON r.id_usuario = u.id
            WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." {$where}
            GROUP BY u.id ORDER BY u.id DESC LIMIT {$limit} OFFSET {$offset}", implode('&', $params));
        return $read;
    }

    public function contarClientesByCompany(string $busca = ''): int
    {
        $params = [];
        $where = $this->filtroBusca($busca, $params);

        $read = new Read();
        $read->FullRead("SELECT COUNT(DISTINCT u.id) AS total FROM usuarios AS u
            INNER JOIN reservas AS r ON r.id_usuario = u.id
            WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." {$where}", implode('&', $params));
        return (int) ($read->getResultSingle()['total'] ?? 0);
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