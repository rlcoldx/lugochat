<?php

namespace Agencia\Close\Models\Feriados;

use Agencia\Close\Conn\Delete;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class Feriados extends Model
{
    /**
     * Baixa feriados públicos da API Nager.Date e grava na tabela `feriados` com id_motel = 0.
     * Antes de inserir, remove todos os registros com id_motel = 0.
     *
     * @see https://date.nager.at/api/v3/PublicHolidays/{year}/{countryCode}
     */
    public function syncFromNagerAt(int $year, string $countryCode = 'BR'): array
    {
        $countryCode = strtoupper(preg_replace('/[^A-Za-z]/', '', $countryCode));
        if (strlen($countryCode) !== 2) {
            return ['success' => false, 'message' => 'Código do país inválido.', 'api_count' => 0, 'inserted' => 0];
        }

        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$countryCode}";
        $body = $this->httpGet($url);
        if ($body === null) {
            return ['success' => false, 'message' => 'Falha ao obter dados da API.', 'api_count' => 0, 'inserted' => 0];
        }

        $items = json_decode($body, true);
        if (!is_array($items)) {
            return ['success' => false, 'message' => 'Resposta da API inválida.', 'api_count' => 0, 'inserted' => 0];
        }

        $apiCount = count($items);
        if ($apiCount === 0) {
            return [
                'success' => false,
                'message' => 'A API retornou lista vazia (nenhum feriado para este ano/país).',
                'api_count' => 0,
                'inserted' => 0,
                'year' => $year,
                'country' => $countryCode,
            ];
        }

        $delete = new Delete();
        $delete->ExeDelete('feriados', 'WHERE id_motel = :id_motel', 'id_motel=0');

        $inserted = 0;
        foreach ($items as $row) {
            if (empty($row['date'])) {
                continue;
            }
            $nome = isset($row['localName']) && $row['localName'] !== ''
                ? (string) $row['localName']
                : (string) ($row['name'] ?? '');
            $nomeDb = $this->truncateParaColunaFeriado($nome, 50);

            $read = new Read();
            $read->FullRead(
                "INSERT INTO feriados (`id_motel`, `date`, `feriado`) VALUES (0, :dt, :nm)",
                'dt=' . $row['date'] . '&nm=' . rawurlencode($nomeDb)
            );
            if ($read->getResult() !== null) {
                $inserted++;
            }
        }

        if ($inserted === 0) {
            return [
                'success' => false,
                'message' => 'A API retornou feriados, mas nenhum registro foi inserido. Verifique se a tabela `feriados` existe, permissões do banco e se há erros SQL (charset/colunas).',
                'year' => $year,
                'country' => $countryCode,
                'api_count' => $apiCount,
                'inserted' => 0,
            ];
        }

        return [
            'success' => true,
            'message' => 'Sincronizado.',
            'year' => $year,
            'country' => $countryCode,
            'api_count' => $apiCount,
            'inserted' => $inserted,
        ];
    }

    /**
     * Limita o nome do feriado ao tamanho da coluna (sem conversão de charset).
     */
    private function truncateParaColunaFeriado(string $text, int $maxChars): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxChars, 'UTF-8');
        }
        return substr($text, 0, $maxChars);
    }

    private function httpGet(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 200 || $body === false) {
                return null;
            }
            return $body;
        }

        $ctx = stream_context_create([
            'http' => ['timeout' => 30],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return $body !== false ? $body : null;
    }

    /** Feriados nacionais (somente leitura na tela). */
    public function listarNacionais(): array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT `id_motel`, `date`, `feriado`, `date_create`, `date_update`
            FROM feriados
            WHERE `id_motel` = 0
            ORDER BY `date` ASC",
            null
        );
        return $read->getResult() ?: [];
    }

    /**
     * Feriados nacionais (id_motel = 0) + feriados próprios do motel.
     */
    public function listarParaMotel(int $idMotel): array
    {
        if ($idMotel <= 0) {
            return [];
        }
        $read = new Read();
        $read->FullRead(
            "SELECT `id_motel`, `date`, `feriado`, `date_create`, `date_update`
            FROM feriados
            WHERE `id_motel` = 0 OR `id_motel` = :mid
            ORDER BY `date` ASC",
            'mid=' . $idMotel
        );
        return $read->getResult() ?: [];
    }

    public function existeProprioDoMotel(int $idMotel, string $date): bool
    {
        if ($idMotel <= 0) {
            return false;
        }
        $read = new Read();
        $read->FullRead(
            "SELECT 1 FROM feriados WHERE `id_motel` = :mid AND `date` = :dt LIMIT 1",
            'mid=' . $idMotel . '&dt=' . $date
        );
        $r = $read->getResult();
        return !empty($r);
    }

    public function obterProprioDoMotel(int $idMotel, string $date): ?array
    {
        if ($idMotel <= 0) {
            return null;
        }
        $read = new Read();
        $read->FullRead(
            "SELECT `id_motel`, `date`, `feriado` FROM feriados WHERE `id_motel` = :mid AND `date` = :dt LIMIT 1",
            'mid=' . $idMotel . '&dt=' . $date
        );
        $r = $read->getResult();
        return $r ? $r[0] : null;
    }

    public function inserirProprio(int $idMotel, string $date, string $nomeUtf8): bool
    {
        if ($idMotel <= 0) {
            return false;
        }
        $nomeDb = $this->truncateParaColunaFeriado($nomeUtf8, 50);
        $read = new Read();
        $read->FullRead(
            "INSERT INTO feriados (`id_motel`, `date`, `feriado`) VALUES (:mid, :dt, :nm)",
            'mid=' . $idMotel . '&dt=' . $date . '&nm=' . rawurlencode($nomeDb)
        );
        return $read->getResult() !== null;
    }

    public function atualizarProprio(int $idMotel, string $dateAntiga, string $dateNova, string $nomeUtf8): bool
    {
        if ($idMotel <= 0 || $this->obterProprioDoMotel($idMotel, $dateAntiga) === null) {
            return false;
        }
        if ($dateNova !== $dateAntiga && $this->existeProprioDoMotel($idMotel, $dateNova)) {
            return false;
        }
        $nomeDb = $this->truncateParaColunaFeriado($nomeUtf8, 50);
        $read = new Read();
        $read->FullRead(
            "UPDATE feriados SET `date` = :dt_novo, `feriado` = :nm
            WHERE `id_motel` = :mid AND `date` = :dt_ant",
            'dt_novo=' . $dateNova
                . '&nm=' . rawurlencode($nomeDb)
                . '&mid=' . $idMotel
                . '&dt_ant=' . $dateAntiga
        );
        return $read->getResult() !== null;
    }

    public function excluirProprio(int $idMotel, string $date): bool
    {
        if ($idMotel <= 0) {
            return false;
        }
        if ($this->obterProprioDoMotel($idMotel, $date) === null) {
            return false;
        }
        $delete = new Delete();
        $delete->ExeDelete('feriados', 'WHERE `id_motel` = :mid AND `date` = :dt', 'mid=' . $idMotel . '&dt=' . $date);
        return $delete->getResult() !== null;
    }
}
