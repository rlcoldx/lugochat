<?php

namespace Agencia\Close\Controllers\Notificacao;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Notificacao\NotificacaoModel;

class NotificacaoController extends Controller
{

    public function index($params)
    {
        $this->setParams($params);
        $this->render('components/notificacao/criar.twig', []);
    }


    public function enviarNotificacao($params)
    {
        $this->setParams($params);

        $model = new NotificacaoModel();
        $offset = 0;
        $limit = 10000;

        do {
            $usuarios = $model->getUsersID($offset, $limit)->getResult();
            if (empty($usuarios)) {
                break;
            }

            $codes = array();
            foreach ($usuarios as $usuario) {
                $codes[] = $usuario['pushKey'];
            }

            if (!empty($codes)) {
                $this->sendNotificacao($codes, $params['titulo'], $params['mensagem']);
            }

            $offset += $limit;
        } while (count($usuarios) === $limit);

        echo json_encode([
            'status' => 'success',
            'message' => 'Notificações enviadas com sucesso!'
        ]);
    }

    public function sendNotificacao($codes, $titulo, $mensagem){

        $data = [
            "app_id" => "121ec2f2-6d96-477f-adc0-8fa6b3c58c74",
            "contents" => ["en" => $mensagem],
            "headings" => ["en" => $titulo],
            "include_subscription_ids" => $codes
        ];
        $jsonData = json_encode($data);


        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.onesignal.com/notifications',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Key NGJhMjEyY2ItMzU5OS00OGI3LThhODUtMTI1M2ZiMmRhOWIw'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;

    }

}