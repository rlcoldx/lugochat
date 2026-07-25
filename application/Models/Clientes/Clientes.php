<?php

namespace Agencia\Close\Models\Clientes;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

/**
 * Resultado de listagem compatível com Read::getResult().
 */
class ClientesListResult
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getResult()
    {
        return $this->rows ?: null;
    }
}

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
            $where .= " AND ban.banido = 1 ";
        } elseif ($banido === '0') {
            $where .= " AND ban.banido IS NULL ";
        }

        return $where;
    }

    private function orderDir(array $filters): string
    {
        $dir = strtoupper(trim((string) ($filters['dir'] ?? 'DESC')));
        return ($dir === 'ASC') ? 'ASC' : 'DESC';
    }

    private function orderCampo(array $filters): string
    {
        $order = trim((string) ($filters['order'] ?? 'data'));
        return in_array($order, ['nome', 'qtd_reservas', 'data'], true) ? $order : 'data';
    }

    private function sqlJoinBanidos(): string
    {
        return "LEFT JOIN (
            SELECT id_usuario, 1 AS banido
            FROM usuarios_banidos
            WHERE status = 'ativo'
            GROUP BY id_usuario
        ) ban ON ban.id_usuario = u.id";
    }

    /**
     * Listagem rápida: ordena/pagina só em usuarios e depois busca contagens dos IDs da página.
     */
    private function getClientesLimitFirst(int $limit, int $offset, array $filters, bool $porEmpresa): Read
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $dir = $this->orderDir($filters);
        $campo = $this->orderCampo($filters);
        $coluna = ($campo === 'nome') ? 'u.nome' : 'u.data';
        $whereMotel = $porEmpresa ? $this->byCompany('r.id_motel') : '';
        $joinEmpresa = $porEmpresa
            ? "INNER JOIN reservas AS r_emp ON r_emp.id_usuario = u.id " . $this->byCompany('r_emp.id_motel')
            : '';
        $distinct = $porEmpresa ? 'DISTINCT ' : '';

        $sql = "SELECT u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data,
            COALESCE(MAX(ban.banido), 0) AS banido,
            COUNT(DISTINCT CASE WHEN p.id IS NOT NULL THEN r.id END) AS qtd_reservas
            FROM (
                SELECT {$distinct}u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data
                FROM usuarios AS u
                {$this->sqlJoinBanidos()}
                {$joinEmpresa}
                WHERE u.tipo = '1' {$where}
                ORDER BY {$coluna} {$dir}, u.id DESC
                LIMIT {$limit} OFFSET {$offset}
            ) u
            {$this->sqlJoinBanidos()}
            LEFT JOIN reservas r ON r.id_usuario = u.id {$whereMotel}
            LEFT JOIN pagamentos p ON p.id_reserva = r.id AND p.pagamento_status = 'approved'
            GROUP BY u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data
            ORDER BY {$coluna} {$dir}, u.id DESC";

        $read = new Read();
        $read->FullRead($sql, implode('&', $params));
        return $read;
    }

    /**
     * Ordenação por quantidade de reservas pagas, paginando em dois grupos (com pagamento / sem).
     */
    private function getClientesPorQtd(int $limit, int $offset, array $filters, bool $porEmpresa): ClientesListResult
    {
        $dir = $this->orderDir($filters);

        $payers = $this->contarPayers($filters, $porEmpresa);
        $total = $porEmpresa ? $this->contarClientesByCompany($filters) : $this->contarClientes($filters);
        $zeros = max(0, $total - $payers);

        $rows = [];

        if ($dir === 'DESC') {
            // Quem tem reservas pagas primeiro
            if ($offset < $payers) {
                $takePayers = min($limit, $payers - $offset);
                $rows = array_merge($rows, $this->fetchPayersPage($takePayers, $offset, $filters, $porEmpresa, 'DESC'));
                $rest = $limit - count($rows);
                if ($rest > 0 && $zeros > 0) {
                    $rows = array_merge($rows, $this->fetchZerosPage($rest, 0, $filters, $porEmpresa));
                }
            } else {
                $rows = $this->fetchZerosPage($limit, $offset - $payers, $filters, $porEmpresa);
            }
        } else {
            // Sem reservas pagas primeiro
            if ($offset < $zeros) {
                $takeZeros = min($limit, $zeros - $offset);
                $rows = array_merge($rows, $this->fetchZerosPage($takeZeros, $offset, $filters, $porEmpresa));
                $rest = $limit - count($rows);
                if ($rest > 0 && $payers > 0) {
                    $rows = array_merge($rows, $this->fetchPayersPage($rest, 0, $filters, $porEmpresa, 'ASC'));
                }
            } else {
                $rows = $this->fetchPayersPage($limit, $offset - $zeros, $filters, $porEmpresa, 'ASC');
            }
        }

        return new ClientesListResult($rows);
    }

    private function contarPayers(array $filters, bool $porEmpresa): int
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $whereMotel = $porEmpresa ? $this->byCompany('r.id_motel') : '';
        $joinEmpresa = $porEmpresa
            ? "INNER JOIN reservas AS r_emp ON r_emp.id_usuario = u.id " . $this->byCompany('r_emp.id_motel')
            : '';

        $read = new Read();
        $read->FullRead(
            "SELECT COUNT(DISTINCT u.id) AS total
             FROM usuarios u
             {$this->sqlJoinBanidos()}
             {$joinEmpresa}
             INNER JOIN reservas r ON r.id_usuario = u.id {$whereMotel}
             INNER JOIN pagamentos p ON p.id_reserva = r.id AND p.pagamento_status = 'approved'
             WHERE u.tipo = '1' {$where}",
            implode('&', $params)
        );
        return (int) ($read->getResultSingle()['total'] ?? 0);
    }

    private function fetchPayersPage(int $limit, int $offset, array $filters, bool $porEmpresa, string $dir): array
    {
        if ($limit <= 0) {
            return [];
        }
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $whereMotel = $porEmpresa ? $this->byCompany('r.id_motel') : '';
        $dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

        $read = new Read();
        $read->FullRead(
            "SELECT u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data,
                    COALESCE(ban.banido, 0) AS banido,
                    res.qtd_reservas
             FROM (
                SELECT r.id_usuario, COUNT(DISTINCT r.id) AS qtd_reservas
                FROM reservas r
                INNER JOIN pagamentos p ON p.id_reserva = r.id AND p.pagamento_status = 'approved'
                WHERE 1=1 {$whereMotel}
                GROUP BY r.id_usuario
             ) res
             INNER JOIN usuarios u ON u.id = res.id_usuario
             {$this->sqlJoinBanidos()}
             WHERE u.tipo = '1' {$where}
             ORDER BY res.qtd_reservas {$dir}, u.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            implode('&', $params)
        );
        return $read->getResult() ?: [];
    }

    private function fetchZerosPage(int $limit, int $offset, array $filters, bool $porEmpresa): array
    {
        if ($limit <= 0) {
            return [];
        }
        $params = [];
        $where = $this->montarFiltros($filters, $params);
        $whereMotel = $porEmpresa ? $this->byCompany('r.id_motel') : '';
        $joinEmpresa = $porEmpresa
            ? "INNER JOIN reservas AS r_emp ON r_emp.id_usuario = u.id " . $this->byCompany('r_emp.id_motel')
            : '';
        $distinct = $porEmpresa ? 'DISTINCT ' : '';

        $read = new Read();
        $read->FullRead(
            "SELECT {$distinct}u.id, u.nome, u.email, u.cpf, u.telefone, u.status, u.data,
                    COALESCE(ban.banido, 0) AS banido,
                    0 AS qtd_reservas
             FROM usuarios u
             {$this->sqlJoinBanidos()}
             {$joinEmpresa}
             WHERE u.tipo = '1' {$where}
             AND u.id NOT IN (
                SELECT r.id_usuario
                FROM reservas r
                INNER JOIN pagamentos p ON p.id_reserva = r.id AND p.pagamento_status = 'approved'
                WHERE 1=1 {$whereMotel}
                GROUP BY r.id_usuario
             )
             ORDER BY u.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            implode('&', $params)
        );
        return $read->getResult() ?: [];
    }

    /**
     * @return Read|ClientesListResult
     */
    public function getClientes(int $limit = 25, int $offset = 0, array $filters = [])
    {
        if ($this->orderCampo($filters) === 'qtd_reservas') {
            return $this->getClientesPorQtd($limit, $offset, $filters, false);
        }
        return $this->getClientesLimitFirst($limit, $offset, $filters, false);
    }

    /**
     * @return Read|ClientesListResult
     */
    public function getClientesByCompany(int $limit = 25, int $offset = 0, array $filters = [])
    {
        if ($this->orderCampo($filters) === 'qtd_reservas') {
            return $this->getClientesPorQtd($limit, $offset, $filters, true);
        }
        return $this->getClientesLimitFirst($limit, $offset, $filters, true);
    }

    public function contarClientes(array $filters = []): int
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);

        $read = new Read();
        $read->FullRead(
            "SELECT COUNT(*) AS total
             FROM usuarios AS u
             {$this->sqlJoinBanidos()}
             WHERE u.tipo = '1' {$where}",
            implode('&', $params)
        );
        return (int) ($read->getResultSingle()['total'] ?? 0);
    }

    public function contarClientesByCompany(array $filters = []): int
    {
        $params = [];
        $where = $this->montarFiltros($filters, $params);

        $read = new Read();
        $read->FullRead(
            "SELECT COUNT(DISTINCT u.id) AS total
             FROM usuarios AS u
             {$this->sqlJoinBanidos()}
             INNER JOIN reservas AS r ON r.id_usuario = u.id
             WHERE u.tipo = '1' ".$this->byCompany('r.id_motel')." {$where}",
            implode('&', $params)
        );
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
                    (SELECT COUNT(DISTINCT u.id)
                     FROM usuarios u
                     INNER JOIN reservas r ON r.id_usuario = u.id
                     WHERE u.tipo = '1' {$empresa}) AS total,
                    (SELECT COUNT(DISTINCT u.id)
                     FROM usuarios u
                     INNER JOIN reservas r ON r.id_usuario = u.id
                     WHERE u.tipo = '1' AND u.status = 'Inativo' {$empresa}) AS inativos,
                    (SELECT COUNT(DISTINCT b.id_usuario)
                     FROM usuarios_banidos b
                     INNER JOIN usuarios u ON u.id = b.id_usuario AND u.tipo = '1'
                     INNER JOIN reservas r ON r.id_usuario = u.id
                     WHERE b.status = 'ativo' {$empresa}) AS banidos"
            );
        } else {
            $read->FullRead(
                "SELECT
                    (SELECT COUNT(*) FROM usuarios WHERE tipo = '1') AS total,
                    (SELECT COUNT(*) FROM usuarios WHERE tipo = '1' AND status = 'Inativo') AS inativos,
                    (SELECT COUNT(DISTINCT b.id_usuario)
                     FROM usuarios_banidos b
                     INNER JOIN usuarios u ON u.id = b.id_usuario AND u.tipo = '1'
                     WHERE b.status = 'ativo') AS banidos"
            );
        }

        $row = $read->getResultSingle();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'inativos' => (int) ($row['inativos'] ?? 0),
            'banidos' => (int) ($row['banidos'] ?? 0),
        ];
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
