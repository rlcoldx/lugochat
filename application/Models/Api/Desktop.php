<?php

namespace Agencia\Close\Models\Api;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class Desktop extends Model
{
    /**
     * Busca usuário do app desktop (tipo 3 — motel/equipe).
     */
    public function getUsuarioByEmail(string $email): Read
    {
        $read = new Read();
        $read->FullRead(
            "SELECT id, nome, email, empresa, tipo, status, logo, codigo, cargo
             FROM usuarios
             WHERE email = :email AND tipo = '3'
             LIMIT 1",
            'email=' . urlencode($email)
        );
        return $read;
    }

    public function senhaConfere(string $email, string $senhaHash): bool
    {
        $read = new Read();
        $read->FullRead(
            "SELECT id FROM usuarios
             WHERE email = :email AND tipo = '3'
             AND (senha = :senha OR senhapadrao = :senha)
             LIMIT 1",
            'email=' . urlencode($email) . '&senha=' . urlencode($senhaHash)
        );

        return !empty($read->getResultSingle()['id']);
    }
}
