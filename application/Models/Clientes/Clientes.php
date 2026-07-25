<?php

namespace Agencia\Close\Models\Clientes;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class Clientes extends Model 
{

    /**
     * Monta filtros de busca, status, banimento.
     * @param array $filters search|status|banido
     * @param array $params Recebe (por referência) os placeholders no formato do FullRead.
     */
    private function montarFiltros(array $filters, array &$params): string
    {
        $where = '';

        $busca = trim((string) ($filters['search'] ?? ''));
        if ($busca !== '') {
            // FullRead usa parse_str (espera query string codificada): urlencode evita que
            // trechos como "%da" sejam interpretados como sequência hex e corrompam o termo.
            $params[] = 'busca=' . urlencode('%' . $busca . '%');
            $where .= " AND (u.nome LIKE :busca OR u.email LIKE :busca OR u.cpf LIKE :busca OR u.telefone LIKE :busca) ";
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'Ativo' || $status === 'Inativo') {
            $params[] = 'status_user=' . urlencode($status);
            $where .= " AND u.status = :status_user ";
        }

        $banido = trim((string) ($filters['banido'] ?? ''));
        if ($banido === '1') {
            $where .= " AND EXISTS (
                SELECT 1 FROM usuarios_banidos b
                WHERE b.id_usuario = u.id AND b.status = 'ativo'
            ) ";
        } elseif ($banido === '0') {
            $where .= " AND NOT EXISTS (
                SELECT 1 FROM usuarios_banidos b
                WHERE b.id_usuario = u.id AND b.status = 'ativo'
            ) ";
        }

        return $where;
    }

    private function orderBySql(array $filters): string
    {
        $order = trim((string) ($filters['order'] ?? 'data'));
        $dir = strtoupper(trim((string) ($filters['dir'] ?? 'DESC')));
        if ($dir !== 'ASC' && $dir !== 'DESC') {
            $dir = 'DESC';
        }

        $map = [
            'nome' => 'u.nome',
            'qtd_reservas' => 'qtd_reservas',
            'data' => 'u.data',
        ];

        $coluna = $map[$order] ?? 'u.data';
        return " ORDER BY {$coluna} {$dir}, u.id DESC ";
    }

    private function selectListagem(string $whereCompany = ''): string
    {
        return "SELECT u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data,
            (SELECT COUNT(*) FROM usuarios_banidos b WHERE b.id_usuario = u.id AND b.status = 'ativo') AS banido,
            (
                SELECT COUNT(DISTINCT r.id)
                FROM reservas r
                INNER JOIN pagamentos p ON p.id_reserva = r.id
                WHERE r.id_usuario = u.id
                AND p.pagamento_status = 'approved'
                {$whereCompany}
            ) AS qtd_reservas";
    }

    public function getClientes(int $limit = 25, int $offset = 0, array $filters = []): Read
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $orderBy = $this->orderBySql($filters);

        $read = new Read();
        $read->FullRead(
            $this->selectListagem() . "
            FROM usuarios AS u
            WHERE u.tipo = '1' {$where}
            {$orderBy}
            LIMIT {$limit} OFFSET {$offset}",
            implode('&', $params)
        );
        return $read;
    }

    public function contarClientes(array $filters = []): int
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);

        $read = new Read();
        $read->FullRead("SELECT COUNT(*) AS total FROM usuarios AS u
            WHERE u.tipo = '1' {$where}", implode('&', $params));
        return (int) ($read->getResultSingle()['total'] ?? 0);
    }

    /**
     * Contadores gerais (independentes dos filtros da listagem).
     * @return array{total:int,inativos:int,banidos:int}
     */
    public function getResumoContadores(bool $porEmpresa = false): array
    {
        $read = new Read();

        if ($porEmpresa) {
            $empresa = $this->byCompany('r.id_motel');
            $read->FullRead(
                "SELECT
                    COUNT(DISTINCT u.id) AS total,
                    COUNT(DISTINCT CASE WHEN u.status = 'Inativo' THEN u.id END) AS inativos,
                    COUNT(DISTINCT CASE WHEN EXISTS (
                        SELECT 1 FROM usuarios_banidos b
                        WHERE b.id_usuario = u.id AND b.status = 'ativo'
                    ) THEN u.id END) AS banidos
                 FROM usuarios AS u
                 INNER JOIN reservas AS r ON r.id_usuario = u.id
                 WHERE u.tipo = '1' {$empresa}"
            );
        } else {
            $read->FullRead(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN u.status = 'Inativo' THEN 1 ELSE 0 END) AS inativos,
                    SUM(CASE WHEN EXISTS (
                        SELECT 1 FROM usuarios_banidos b
                        WHERE b.id_usuario = u.id AND b.status = 'ativo'
                    ) THEN 1 ELSE 0 END) AS banidos
                 FROM usuarios AS u
                 WHERE u.tipo = '1'"
            );
        }

        $row = $read->getResultSingle();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'inativos' => (int) ($row['inativos'] ?? 0),
            'banidos' => (int) ($row['banidos'] ?? 0),
        ];
    }

    public function getClientesByCompany(int $limit = 25, int $offset = 0, array $filters = []): Read
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $whereMotel = $this->byCompany('r.id_motel');
        $orderBy = $this->orderBySql($filters);

        $read = new Read();
        $read->FullRead(
            $this->selectListagem($whereMotel) . "
            FROM usuarios AS u
            INNER JOIN reservas AS r ON r.id_usuario = u.id
            WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." {$where}
            GROUP BY u.id {$orderBy}
            LIMIT {$limit} OFFSET {$offset}",
            implode('&', $params)
        );
        return $read;
    }

    public function contarClientesByCompany(array $filters = []): int
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);

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
