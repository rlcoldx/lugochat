<?php

namespace Agencia\Close\Models\MotelFechamentos;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class MotelFechamentos extends Model
{
    public function listarGrupos(): array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT
                f.id_grupo,
                MIN(f.date_fechamento) AS date_fechamento,
                MIN(f.proprietario) AS proprietario,
                COUNT(*) AS qtd_moteis,
                GROUP_CONCAT(u.nome ORDER BY u.nome SEPARATOR ', ') AS moteis_nomes,
                MIN(f.date_create) AS date_create
            FROM motel_fechamento f
            INNER JOIN usuarios u ON u.id = f.id_motel
            GROUP BY f.id_grupo
            ORDER BY MIN(f.date_fechamento) DESC, MIN(f.proprietario) ASC"
        );
        return $read->getResult() ?: [];
    }

    public function listarMoteisPorProprietario(string $proprietario): array
    {
        $proprietario = $this->normalizarProprietario($proprietario);
        if ($proprietario === '') {
            return [];
        }

        $read = new Read();
        $read->FullRead(
            "SELECT id, nome, proprietario
            FROM usuarios
            WHERE tipo = '2'
            AND TRIM(proprietario) = :prop
            AND `status` = 'Ativo'
            ORDER BY nome ASC",
            'prop=' . rawurlencode($proprietario)
        );
        return $read->getResult() ?: [];
    }

    public function obterGrupo(string $idGrupo): ?array
    {
        $idGrupo = trim($idGrupo);
        if ($idGrupo === '') {
            return null;
        }

        $read = new Read();
        $read->FullRead(
            "SELECT id_motel, proprietario, date_fechamento
            FROM motel_fechamento
            WHERE id_grupo = :grupo
            ORDER BY id_motel ASC",
            'grupo=' . rawurlencode($idGrupo)
        );
        $rows = $read->getResult();
        if (!$rows) {
            return null;
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['id_motel'];
        }

        return [
            'id_grupo' => $idGrupo,
            'date_fechamento' => $rows[0]['date_fechamento'],
            'proprietario' => $rows[0]['proprietario'],
            'id_moteis' => $ids,
        ];
    }

    /**
     * @param int[] $idMoteis
     */
    public function salvarGrupo(string $date, string $proprietario, array $idMoteis, ?string $idGrupoEdicao = null): array
    {
        $proprietario = $this->normalizarProprietario($proprietario);
        $idMoteis = array_values(array_unique(array_filter(array_map('intval', $idMoteis), static function ($id) {
            return $id > 0;
        })));

        if ($proprietario === '' || $idMoteis === []) {
            return ['ok' => false, 'code' => 'validacao'];
        }

        $moteisValidos = $this->listarMoteisPorProprietario($proprietario);
        $idsPermitidos = array_column($moteisValidos, 'id');
        $idsPermitidos = array_map('intval', $idsPermitidos);

        foreach ($idMoteis as $id) {
            if (!in_array($id, $idsPermitidos, true)) {
                return ['ok' => false, 'code' => 'motel_invalido'];
            }
        }

        foreach ($idMoteis as $idMotel) {
            if ($this->existeFechamentoMotelNaData($idMotel, $date, $idGrupoEdicao)) {
                return ['ok' => false, 'code' => 'duplicado'];
            }
        }

        if ($idGrupoEdicao !== null && $idGrupoEdicao !== '') {
            $this->excluirGrupo($idGrupoEdicao);
            $idGrupo = $idGrupoEdicao;
        } else {
            $idGrupo = bin2hex(random_bytes(20));
        }

        foreach ($idMoteis as $idMotel) {
            $create = new Create();
            $create->ExeCreate('motel_fechamento', [
                'id_grupo' => $idGrupo,
                'id_motel' => $idMotel,
                'proprietario' => $proprietario,
                'date_fechamento' => $date,
            ]);
            if (!$create->getResult()) {
                return ['ok' => false, 'code' => 'salvar'];
            }
        }

        return ['ok' => true, 'id_grupo' => $idGrupo];
    }

    public function excluirGrupo(string $idGrupo): bool
    {
        $idGrupo = trim($idGrupo);
        if ($idGrupo === '') {
            return false;
        }
        $delete = new Delete();
        $delete->ExeDelete('motel_fechamento', 'WHERE id_grupo = :grupo', 'grupo=' . rawurlencode($idGrupo));
        return $delete->getResult() !== null;
    }

    private function existeFechamentoMotelNaData(int $idMotel, string $date, ?string $idGrupoIgnorar): bool
    {
        $read = new Read();
        if ($idGrupoIgnorar !== null && $idGrupoIgnorar !== '') {
            $read->FullRead(
                "SELECT 1 FROM motel_fechamento
                WHERE id_motel = :mid AND date_fechamento = :dt AND id_grupo <> :grupo
                LIMIT 1",
                'mid=' . $idMotel . '&dt=' . $date . '&grupo=' . rawurlencode($idGrupoIgnorar)
            );
        } else {
            $read->FullRead(
                "SELECT 1 FROM motel_fechamento WHERE id_motel = :mid AND date_fechamento = :dt LIMIT 1",
                'mid=' . $idMotel . '&dt=' . $date
            );
        }
        return !empty($read->getResult());
    }

    private function normalizarProprietario(string $proprietario): string
    {
        $proprietario = trim($proprietario);
        if ($proprietario === '') {
            return '';
        }
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($proprietario, 'UTF-8');
        }
        return strtoupper($proprietario);
    }
}
