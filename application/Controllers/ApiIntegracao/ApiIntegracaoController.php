<?php

namespace Agencia\Close\Controllers\ApiIntegracao;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\ApiIntegracao\ApiIntegracao;

class ApiIntegracaoController extends Controller
{
    private function getMotelId()
    {
        return (isset($_SESSION['busca_perfil_tipo']) && $_SESSION['busca_perfil_tipo'] != 0) 
            ? $_SESSION['busca_perfil_empresa'] 
            : null;
    }

    /**
     * Online se date_update está a até 10s da hora atual; senão Offline.
     * Sem date_update válido considera Offline.
     */
    private static function tokenIsOnline(?string $dateUpdate): bool
    {
        if ($dateUpdate === null || trim($dateUpdate) === '') {
            return false;
        }
        $ts = strtotime($dateUpdate);
        if ($ts === false) {
            return false;
        }
        $diff = time() - $ts;
        if ($diff < 0) {
            return true;
        }
        return $diff <= 10;
    }

    /**
     * @param array<int, array<string, mixed>>|null $tokens
     * @return array<int, array<string, mixed>>
     */
    private function enrichTokensWithStatus(?array $tokens): array
    {
        if ($tokens === null || $tokens === []) {
            return [];
        }
        foreach ($tokens as &$t) {
            $ref = $t['date_update'] ?? null;
            $t['api_online'] = self::tokenIsOnline(is_string($ref) ? $ref : null);
        }
        unset($t);
        return $tokens;
    }

    public function index($params)
    {
        $this->setParams($params);
        
        $model = new ApiIntegracao();
        $tokens = $model->getTokens($this->getMotelId())->getResult();
        $tokens = $this->enrichTokensWithStatus($tokens);
        
        $this->render('pages/api-integracao/index.twig', [
            'titulo' => 'Integração API - Tokens',
            'tokens' => $tokens
        ]);
    }

    public function tokensRefresh($params)
    {
        $this->setParams($params);

        $model = new ApiIntegracao();
        $tokens = $model->getTokens($this->getMotelId())->getResult();
        $tokens = $this->enrichTokensWithStatus($tokens);

        $out = [];
        foreach ($tokens as $t) {
            $online = !empty($t['api_online']);
            $out[] = [
                'id' => (int) ($t['id'] ?? 0),
                'acessos' => (int) ($t['acessos'] ?? 0),
                'online' => $online,
                'label' => $online ? 'Online' : 'Offline',
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['tokens' => $out], JSON_UNESCAPED_UNICODE);
    }

    public function criar()
    {
        $model = new ApiIntegracao();
        $moteis = $model->getMoteis()->getResult();
        
        $this->render('components/api-integracao/form.twig', [
            'moteis' => $moteis,
            'motel_id_logado' => $this->getMotelId()
        ]);
    }

    public function editar($params)
    {
        $this->setParams($params);
        
        $model = new ApiIntegracao();
        $token = $model->getTokenById($params['id'], $this->getMotelId())->getResult()[0] ?? null;
        
        if (!$token) {
            echo json_encode(['erro' => 'Token não encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $moteis = $model->getMoteis()->getResult();
        
        $this->render('components/api-integracao/form.twig', [
            'token' => $token,
            'moteis' => $moteis,
            'motel_id_logado' => $this->getMotelId()
        ]);
    }

    public function salvar($params)
    {
        $this->setParams($params);
        
        $model = new ApiIntegracao();
        
        // Prepara os dados
        $dados = [
            'id_motel' => $params['id_motel'],
            'sistema' => $params['sistema']
        ];
        
        // Se for criação, gera um novo token
        if ($params['id'] == '-1') {
            $dados['token'] = $this->generate($params['id_motel']);
            $result = $model->createToken($dados);
            echo json_encode(['success' => true, 'id' => $result], JSON_UNESCAPED_UNICODE);
        } else {
            // Atualização
            $result = $model->updateToken($dados, $params['id'], $this->getMotelId());
            echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deletar($params)
    {
        $this->setParams($params);
        
        $model = new ApiIntegracao();
        $result = $model->deleteToken($params['id'], $this->getMotelId());
        
        echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
    }

    // GERA TOKEN PARA CHAVES
    public function generate($id_motel)
    {
        $rawToken = $id_motel . '|' . bin2hex(random_bytes(8)) . '|' . microtime(true);
        return sha1($rawToken);
    }
}

