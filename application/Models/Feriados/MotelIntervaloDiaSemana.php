<?php

namespace Agencia\Close\Models\Feriados;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class MotelIntervaloDiaSemana extends Model
{
    private function truncarNome(string $text, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $max, 'UTF-8');
        }
        return substr($text, 0, $max);
    }

    public static function nomeDiaPt(int $d): string
    {
        $map = [
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
        return $map[$d] ?? '';
    }

    public function listarPorMotel(int $idMotel): array
    {
        if ($idMotel <= 0) {
            return [];
        }
        $read = new Read();
        $read->FullRead(
            'SELECT id, id_motel, nome, dia_semana_inicio, hora_inicio, dia_semana_fim, hora_fim, date_create, date_update
            FROM motel_intervalo_dia_semana
            WHERE id_motel = :mid
            ORDER BY dia_semana_inicio ASC, hora_inicio ASC, id ASC',
            'mid=' . $idMotel
        );
        return $read->getResult() ?: [];
    }

    public function obterDoMotel(int $idMotel, int $id): ?array
    {
        if ($idMotel <= 0 || $id <= 0) {
            return null;
        }
        $read = new Read();
        $read->FullRead(
            'SELECT id, id_motel, nome, dia_semana_inicio, hora_inicio, dia_semana_fim, hora_fim
            FROM motel_intervalo_dia_semana
            WHERE id = :id AND id_motel = :mid
            LIMIT 1',
            'id=' . $id . '&mid=' . $idMotel
        );
        $r = $read->getResult();
        return $r ? $r[0] : null;
    }

    /**
     * @param array<int, array{dia_semana_inicio: int, hora_inicio: string, dia_semana_fim: int, hora_fim: string}> $linhas
     */
    public function inserirLote(int $idMotel, string $nome, array $linhas): bool
    {
        if ($idMotel <= 0 || $linhas === []) {
            return false;
        }
        $nome = $this->truncarNome($nome, 50);
        foreach ($linhas as $row) {
            if (!$this->validarLinha($row) || !$this->inserirUm($idMotel, $nome, $row)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array{dia_semana_inicio: int, hora_inicio: string, dia_semana_fim: int, hora_fim: string} $row
     */
    public function atualizar(int $idMotel, int $id, string $nome, array $row): bool
    {
        if ($this->obterDoMotel($idMotel, $id) === null || !$this->validarLinha($row)) {
            return false;
        }
        $nome = $this->truncarNome($nome, 50);
        $hi = $this->normalizarHora($row['hora_inicio']);
        $hf = $this->normalizarHora($row['hora_fim']);
        $update = new Update();
        $update->ExeUpdate(
            'motel_intervalo_dia_semana',
            [
                'nome' => $nome,
                'dia_semana_inicio' => $row['dia_semana_inicio'],
                'hora_inicio' => $hi,
                'dia_semana_fim' => $row['dia_semana_fim'],
                'hora_fim' => $hf,
            ],
            'WHERE id = :id AND id_motel = :mid',
            'id=' . $id . '&mid=' . $idMotel
        );
        return $update->getResult() !== null;
    }

    public function excluir(int $idMotel, int $id): bool
    {
        if ($this->obterDoMotel($idMotel, $id) === null) {
            return false;
        }
        $delete = new Delete();
        $delete->ExeDelete('motel_intervalo_dia_semana', 'WHERE id = :id AND id_motel = :mid', 'id=' . $id . '&mid=' . $idMotel);
        return $delete->getResult() !== null;
    }

    public function textoPeriodo(array $row): string
    {
        $di = (int) ($row['dia_semana_inicio'] ?? 0);
        $df = (int) ($row['dia_semana_fim'] ?? 0);
        $hi = $this->formatarHoraExibicao($row['hora_inicio'] ?? '');
        $hf = $this->formatarHoraExibicao($row['hora_fim'] ?? '');
        return self::nomeDiaPt($di) . ' ' . $hi . ' – ' . self::nomeDiaPt($df) . ' ' . $hf;
    }

    /**
     * @param array{dia_semana_inicio: int, hora_inicio: string, dia_semana_fim: int, hora_fim: string} $row
     */
    private function validarLinha(array $row): bool
    {
        $di = (int) ($row['dia_semana_inicio'] ?? 0);
        $df = (int) ($row['dia_semana_fim'] ?? 0);
        if ($di < 1 || $di > 7 || $df < 1 || $df > 7) {
            return false;
        }
        $hi = isset($row['hora_inicio']) ? trim((string) $row['hora_inicio']) : '';
        $hf = isset($row['hora_fim']) ? trim((string) $row['hora_fim']) : '';
        if (!$this->horaValida($hi) || !$this->horaValida($hf)) {
            return false;
        }
        $hiN = $this->normalizarHora($hi);
        $hfN = $this->normalizarHora($hf);
        $start = $this->minutosDesdeSegundaZero($di, $hiN);
        $end = $this->minutosDesdeSegundaZero($df, $hfN);
        if ($di === $df) {
            return $end > $start;
        }
        if ($df > $di) {
            return $end > $start;
        }
        $end += 7 * 24 * 60;
        return $end > $start;
    }

    /**
     * @param array{dia_semana_inicio: int, hora_inicio: string, dia_semana_fim: int, hora_fim: string} $row
     */
    private function inserirUm(int $idMotel, string $nome, array $row): bool
    {
        $hi = $this->normalizarHora($row['hora_inicio']);
        $hf = $this->normalizarHora($row['hora_fim']);
        $create = new Create();
        $create->ExeCreate('motel_intervalo_dia_semana', [
            'id_motel' => $idMotel,
            'nome' => $nome,
            'dia_semana_inicio' => $row['dia_semana_inicio'],
            'hora_inicio' => $hi,
            'dia_semana_fim' => $row['dia_semana_fim'],
            'hora_fim' => $hf,
        ]);
        $id = $create->getResult();
        return $id !== null && $id !== '' && (int) $id > 0;
    }

    private function minutosDesdeSegundaZero(int $diaIso, string $horaHi): int
    {
        $diaIso = max(1, min(7, $diaIso));
        $h = substr($horaHi, 0, 5);
        $p = explode(':', $h);
        $hm = ((int) ($p[0] ?? 0)) * 60 + ((int) ($p[1] ?? 0));
        return ($diaIso - 1) * 24 * 60 + $hm;
    }

    private function normalizarHora(string $h): string
    {
        $h = strlen($h) > 5 ? substr($h, 0, 5) : $h;
        return $h;
    }

    private function horaValida(string $h): bool
    {
        $h = strlen($h) > 5 ? substr($h, 0, 5) : $h;
        $x = \DateTime::createFromFormat('H:i', $h);
        return $x && $x->format('H:i') === $h;
    }

    private function formatarHoraExibicao(string $dbTime): string
    {
        if (!is_string($dbTime) || $dbTime === '') {
            return '';
        }
        return strlen($dbTime) > 5 ? substr($dbTime, 0, 5) : $dbTime;
    }
}
