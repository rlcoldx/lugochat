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

    public function index($params)
    {
        $this->setParams($params);
        
        $model = new ApiIntegracao();
        $tokens = $model->getTokens($this->getMotelId())->getResult();
        
        $this->render('pages/api-integracao/index.twig', [
            'titulo' => 'Integração API - Tokens',
            'tokens' => $tokens
        ]);
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

