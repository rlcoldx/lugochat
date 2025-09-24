<?php

namespace Agencia\Close\Models\User;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class Permissions extends Model
{
    public function getPermissions(string $user): Read
    {
        $read = new Read();
        $read->FullRead('SELECT permissoes.nome FROM usuario_permissoes
        INNER JOIN usuarios ON usuarios.id = usuario_permissoes.usuario_id
        INNER JOIN permissoes ON usuario_permissoes.permissao_id = permissoes.id
        WHERE usuario_permissoes.usuario_id = :id', "id={$user}");
        return $read;
    }
    
    /**
     * Busca permissões do usuário baseadas no cargo
     */
    public function getPermissionsByUser($userId): Read
    {
        $read = new Read();
        $read->FullRead('
            SELECT DISTINCT p.nome 
            FROM usuarios u
            INNER JOIN cargos c ON c.nome = u.cargo AND c.empresa = u.empresa
            INNER JOIN cargo_permissoes cp ON cp.cargo_id = c.id
            INNER JOIN permissoes p ON p.id = cp.permissao_id
            WHERE u.id = :user_id AND u.status = "Ativo" AND c.status = "Ativo"
        ', "user_id={$userId}");
        return $read;
    }
    
    /**
     * Debug: Verifica se o cargo existe e tem permissões
     */
    public function debugCargoPermissions($userId): Read
    {
        $read = new Read();
        $read->FullRead('
            SELECT 
                u.id as user_id,
                u.nome as user_nome,
                u.cargo as user_cargo,
                u.empresa as user_empresa,
                u.status as user_status,
                c.id as cargo_id,
                c.nome as cargo_nome,
                c.status as cargo_status,
                COUNT(cp.permissao_id) as total_permissoes
            FROM usuarios u
            LEFT JOIN cargos c ON c.nome = u.cargo AND c.empresa = u.empresa
            LEFT JOIN cargo_permissoes cp ON cp.cargo_id = c.id
            WHERE u.id = :user_id
            GROUP BY u.id, c.id
        ', "user_id={$userId}");
        return $read;
    }
}