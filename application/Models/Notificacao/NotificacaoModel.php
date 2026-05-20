<?php

namespace Agencia\Close\Models\Notificacao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Models\Model;

class NotificacaoModel extends Model
{
    public function getUsersID($offset = 0, $limit = 10000)
    {
        $read = new Read();
        $termos = "WHERE tipo = 1 AND pushKey IS NOT NULL AND pushKey <> '' AND pushKey <> 'null' ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $parseString = "limit={$limit}&offset={$offset}";
        $read->exeRead("usuarios", $termos, $parseString);
        return $read;
    }

    /**
     * Administradores da plataforma (tipo 0) — recebem push de qualquer motel.
     *
     * @return array<int, string>
     */
    public function getPushKeysAdministradores(): array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT pushKey FROM usuarios
             WHERE status = 'Ativo'
             AND tipo = '0'
             AND pushKey IS NOT NULL AND pushKey <> '' AND pushKey <> 'null'"
        );

        return $this->extrairPushKeys($read->getResult() ?: []);
    }

    /**
     * Conta do motel (tipo 2, id = motel) e equipe (tipo 3, empresa = motel).
     * Não inclui administradores nem outros motéis.
     *
     * @return array<int, string>
     */
    public function getPushKeysByMotel(int $idMotel): array
    {
        if ($idMotel <= 0) {
            return [];
        }

        $read = new Read();
        $read->FullRead(
            "SELECT pushKey FROM usuarios
             WHERE status = 'Ativo'
             AND pushKey IS NOT NULL AND pushKey <> '' AND pushKey <> 'null'
             AND (
                (tipo = '2' AND id = :id_motel)
                OR (tipo = '3' AND empresa = :id_motel)
             )",
            "id_motel={$idMotel}"
        );

        return $this->extrairPushKeys($read->getResult() ?: []);
    }

    /**
     * Destinatários para reserva com pagamento aprovado: admins + motel/equipe do id_motel.
     *
     * @return array<int, string>
     */
    public function getPushKeysPagamentoAprovado(int $idMotel): array
    {
        return array_values(array_unique(array_merge(
            $this->getPushKeysAdministradores(),
            $this->getPushKeysByMotel($idMotel)
        )));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, string>
     */
    private function extrairPushKeys(array $rows): array
    {
        $keys = [];
        foreach ($rows as $row) {
            if (!empty($row['pushKey'])) {
                $keys[] = $row['pushKey'];
            }
        }

        return array_values(array_unique($keys));
    }

    public function salvarPushKey(int $userId, string $pushKey): bool
    {
        $pushKey = trim($pushKey);
        if ($userId <= 0 || strlen($pushKey) < 10) {
            return false;
        }

        $update = new Update();
        $update->ExeUpdate(
            'usuarios',
            ['pushKey' => $pushKey],
            'WHERE id = :id',
            "id={$userId}"
        );

        return $update->getResult();
    }
}
