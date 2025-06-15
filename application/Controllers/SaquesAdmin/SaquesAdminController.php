<?php

namespace Agencia\Close\Controllers\SaquesAdmin;

use Agencia\Close\Models\User\User;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Saques\SaquesAdminPainel;

class SaquesAdminController extends Controller
{

    public function index($params)
    {
        if ($_SESSION['busca_perfil_tipo'] != 0) {
            $this->router->redirect("/");
        }
        $this->setParams($params);
        $saques = $this->saques();
        $this->render('pages/saquesadmin/lista.twig', ['menu' => 'saques', 'saques' => $saques]);
    }

    private function saques()
    {
        $result = new SaquesAdminPainel();
        return $result->getSaques()->getResult();
    }

    public function statusSave($params)
    {
        $this->setParams($params);
        $result = new SaquesAdminPainel();
        $result->statusSave($params);
    }
}