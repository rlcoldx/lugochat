<?php

namespace Agencia\Close\Models\ApiIntegracao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Models\Model;

class ApiIntegracao extends Model
{
    /**
     * Busca todos os tokens de API
     * @param int|null $id_motel
     * @return Read
     */
    public function getTokens($id_motel = null): Read
    {
        $this->read = new Read();
        
        if ($id_motel) {
            $this->read->FullRead(
                "SELECT a.*, u.nome as nome_motel 
                FROM api_motel a 
                LEFT JOIN usuarios u ON a.id_motel = u.id 
                WHERE a.id_motel = :id_motel 
                ORDER BY a.id DESC",
                "id_motel={$id_motel}"
            );
        } else {
            $this->read->FullRead(
                "SELECT a.*, u.nome as nome_motel 
                FROM api_motel a 
                LEFT JOIN usuarios u ON a.id_motel = u.id 
                ORDER BY a.id DESC"
            );
        }
        
        return $this->read;
    }

    /**
     * Busca um token específico por ID
     * @param int $id
     * @param int|null $id_motel
     * @return Read
     */
    public function getTokenById($id, $id_motel = null): Read
    {
        $this->read = new Read();
        
        if ($id_motel) {
            $this->read->FullRead(
                "SELECT * FROM api_motel WHERE id = :id AND id_motel = :id_motel LIMIT 1",
                "id={$id}&id_motel={$id_motel}"
            );
        } else {
            $this->read->FullRead(
                "SELECT * FROM api_motel WHERE id = :id LIMIT 1",
                "id={$id}"
            );
        }
        
        return $this->read;
    }

    /**
     * Cria um novo token de API
     * @param array $dados
     * @return int|null
     */
    public function createToken($dados)
    {
        // Gera um token único
        if (empty($dados['token'])) {
            $dados['token'] = $this->gerarToken();
        }
        
        $create = new Create();
        $create->ExeCreate('api_motel', $dados);
        
        return $create->getResult();
    }

    /**
     * Atualiza um token existente
     * @param array $dados
     * @param int $id
     * @param int|null $id_motel
     * @return bool
     */
    public function updateToken($dados, $id, $id_motel = null): bool
    {
        $update = new Update();
        
        if ($id_motel) {
            $update->ExeUpdate(
                'api_motel',
                $dados,
                'WHERE id = :id AND id_motel = :id_motel',
                "id={$id}&id_motel={$id_motel}"
            );
        } else {
            $update->ExeUpdate(
                'api_motel',
                $dados,
                'WHERE id = :id',
                "id={$id}"
            );
        }
        
        return $update->getRowCount() > 0;
    }

    /**
     * Deleta um token
     * @param int $id
     * @param int|null $id_motel
     * @return bool
     */
    public function deleteToken($id, $id_motel = null): bool
    {
        $delete = new Delete();
        
        if ($id_motel) {
            $delete->ExeDelete(
                'api_motel',
                'WHERE id = :id AND id_motel = :id_motel',
                "id={$id}&id_motel={$id_motel}"
            );
        } else {
            $delete->ExeDelete(
                'api_motel',
                'WHERE id = :id',
                "id={$id}"
            );
        }
        
        return $delete->getRowCount() > 0;
    }

    /**
     * Verifica se um token é válido
     * @param string $token
     * @return array|null
     */
    public function validarToken($token)
    {
        $read = new Read();
        $read->FullRead(
            "SELECT a.*, u.nome as nome_motel, u.status as motel_status 
            FROM api_motel a 
            LEFT JOIN usuarios u ON a.id_motel = u.id 
            WHERE a.token = :token and u.status = 'Ativo'
            LIMIT 1",
            "token={$token}"
        );
        
        $result = $read->getResult();
        
        if ($result && isset($result[0])) {
            return $result[0];
        }
        
        return null;
    }

    /**
     * Incrementa o contador de acessos de um token
     * @param int $id
     * @return bool
     */
    public function incrementarAcessos($id): bool
    {
        $read = new Read();
        $read->FullRead(
            "UPDATE api_motel SET acessos = acessos + 1 WHERE id = :id",
            "id={$id}"
        );
        
        return true;
    }

    /**
     * Gera um token único
     * @return string
     */
    private function gerarToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Busca todos os motéis ativos
     * @return Read
     */
    public function getMoteis(): Read
    {
        $this->read = new Read();
        $this->read->FullRead(
            "SELECT id, nome FROM usuarios WHERE status = 'Ativo' AND tipo = 2 ORDER BY nome ASC"
        );
        
        return $this->read;
    }
}

