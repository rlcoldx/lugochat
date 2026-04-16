<?php

namespace Agencia\Close\Models\Feriados;

use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class MotelIntervaloDiaSemana extends Model
{
    /** Ordem na semana: seg=1 … dom=7 (ISO, segunda primeiro). */
    private const ORDEM = [
        'seg' => 1,
        'ter' => 2,
        'qua' => 3,
        'qui' => 4,
        'sex' => 5,
        'sab' => 6,
        'dom' => 7,
    ];

    /** Nome completo para exibição na listagem. */
    private const NOME_COMPLETO = [
        'seg' => 'Segunda',
        'ter' => 'Terça',
        'qua' => 'Quarta',
        'qui' => 'Quinta',
        'sex' => 'Sexta',
        'sab' => 'Sábado',
        'dom' => 'Domingo',
    ];

    private function truncarNome(string $text, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $max, 'UTF-8');
        }
        return substr($text, 0, $max);
    }

    /**
     * Normaliza entrada (form ou legado numérico 1–7) para abreviatura seg|ter|…|dom.
     * Retorna string vazia se inválido.
     */
    public static function normalizarDiaAbrev($raw): string
    {
        if ($raw === null) {
            return '';
        }
        $s = strtolower(trim((string) $raw));
        if ($s === '') {
            return '';
        }
        $deNumero = ['1' => 'seg', '2' => 'ter', '3' => 'qua', '4' => 'qui', '5' => 'sex', '6' => 'sab', '7' => 'dom'];
        if (isset($deNumero[$s])) {
            $s = $deNumero[$s];
        }
        return isset(self::ORDEM[$s]) ? $s : '';
    }

    public static function nomeDiaCompletoPorAbrev(string $abrev): string
    {
        $a = self::normalizarDiaAbrev($abrev);
        return $a !== '' ? (self::NOME_COMPLETO[$a] ?? '') : '';
    }

    public function listarPorMotel(int $idMotel): array
    {
        if ($idMotel <= 0) {
            return [];
        }
        $read = new Read();
        $read->FullRead(
            "SELECT id, id_motel, nome, dia_semana_inicio, hora_inicio, dia_semana_fim, hora_fim, date_create, date_update
            FROM motel_intervalo_dia_semana
            WHERE id_motel = :mid
            ORDER BY FIELD(dia_semana_inicio, 'seg','ter','qua','qui','sex','sab','dom'), hora_inicio ASC, id ASC",
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
     * @param array<int, array{dia_semana_inicio: string, hora_inicio: string, dia_semana_fim: string, hora_fim: string}> $linhas
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
     * @param array{dia_semana_inicio: string, hora_inicio: string, dia_semana_fim: string, hora_fim: string} $row
     */
    public function atualizar(int $idMotel, int $id, string $nome, array $row): bool
    {
        if ($this->obterDoMotel($idMotel, $id) === null || !$this->validarLinha($row)) {
            return false;
        }
        $nome = $this->truncarNome($nome, 50);
        $di = self::normalizarDiaAbrev($row['dia_semana_inicio'] ?? '');
        $df = self::normalizarDiaAbrev($row['dia_semana_fim'] ?? '');
        $hi = $this->normalizarHora($row['hora_inicio']);
        $hf = $this->normalizarHora($row['hora_fim']);
        $update = new Update();
        $update->ExeUpdate(
            'motel_intervalo_dia_semana',
            [
                'nome' => $nome,
                'dia_semana_inicio' => $di,
                'hora_inicio' => $hi,
                'dia_semana_fim' => $df,
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
        $di = self::normalizarDiaAbrev($row['dia_semana_inicio'] ?? '');
        $df = self::normalizarDiaAbrev($row['dia_semana_fim'] ?? '');
        $hi = $this->formatarHoraExibicao($row['hora_inicio'] ?? '');
        $hf = $this->formatarHoraExibicao($row['hora_fim'] ?? '');
        $ni = self::nomeDiaCompletoPorAbrev($di);
        $nf = self::nomeDiaCompletoPorAbrev($df);
        return $ni . ' ' . $hi . ' – ' . $nf . ' ' . $hf;
    }

    /**
     * @param array{dia_semana_inicio: string, hora_inicio: string, dia_semana_fim: string, hora_fim: string} $row
     */
    private function validarLinha(array $row): bool
    {
        $di = self::normalizarDiaAbrev($row['dia_semana_inicio'] ?? '');
        $df = self::normalizarDiaAbrev($row['dia_semana_fim'] ?? '');
        if ($di === '' || $df === '') {
            return false;
        }
        $hi = isset($row['hora_inicio']) ? trim((string) $row['hora_inicio']) : '';
        $hf = isset($row['hora_fim']) ? trim((string) $row['hora_fim']) : '';
        if (!$this->horaValida($hi) || !$this->horaValida($hf)) {
            return false;
        }
        $hiN = $this->normalizarHora($hi);
        $hfN = $this->normalizarHora($hf);
        $oDi = self::ORDEM[$di];
        $oDf = self::ORDEM[$df];
        $start = $this->minutosDesdeSegundaZero($oDi, $hiN);
        $end = $this->minutosDesdeSegundaZero($oDf, $hfN);
        if ($oDi === $oDf) {
            return $end > $start;
        }
        if ($oDf > $oDi) {
            return $end > $start;
        }
        $end += 7 * 24 * 60;
        return $end > $start;
    }

    /**
     * @param array{dia_semana_inicio: string, hora_inicio: string, dia_semana_fim: string, hora_fim: string} $row
     */
    private function inserirUm(int $idMotel, string $nome, array $row): bool
    {
        $di = self::normalizarDiaAbrev($row['dia_semana_inicio'] ?? '');
        $df = self::normalizarDiaAbrev($row['dia_semana_fim'] ?? '');
        $hi = $this->normalizarHora($row['hora_inicio']);
        $hf = $this->normalizarHora($row['hora_fim']);
        $create = new Create();
        $create->ExeCreate('motel_intervalo_dia_semana', [
            'id_motel' => $idMotel,
            'nome' => $nome,
            'dia_semana_inicio' => $di,
            'hora_inicio' => $hi,
            'dia_semana_fim' => $df,
            'hora_fim' => $hf,
        ]);
        $id = $create->getResult();
        return $id !== null && $id !== '' && (int) $id > 0;
    }

    private function minutosDesdeSegundaZero(int $ordem1a7, string $horaHi): int
    {
        $ordem1a7 = max(1, min(7, $ordem1a7));
        $h = substr($horaHi, 0, 5);
        $p = explode(':', $h);
        $hm = ((int) ($p[0] ?? 0)) * 60 + ((int) ($p[1] ?? 0));
        return ($ordem1a7 - 1) * 24 * 60 + $hm;
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
