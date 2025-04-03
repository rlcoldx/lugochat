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
        $usuarios = $model->getUsersID()->getResult();
        $codes = array();
        foreach ($usuarios as $usuario) {
            array_push($codes, $usuario['pushKey']); // Montando o array de subscription IDs
        }
        $response = $this->sendNotificacao($codes, $params['titulo'], $params['mensagem']);
        echo $response;
    }

    public function sendNotificacao($codes, $titulo, $mensagem){

        $data = [
            "app_id" => "121ec2f2-6d96-477f-adc0-8fa6b3c58c74",
            "android_accent_color" => "FF0000FF",
            "small_icon" => "icon", 
            "ios_badgeType" => "Increase",
            "ios_badgeCount" => 1, // Aumenta o badge em 1
            "ios_sound" => "default", // Som padrão para iOS
            "chrome_web_icon" => DOMAIN."/view/assets/images/favicon.png", // Ícone para Web Push
            "large_icon" => DOMAIN."/view/assets/images/favicon.png", // Ícone grande para Android
            "big_picture" => DOMAIN."/view/assets/images/favicon.png", // Imagem na notificação
            "mutable_content" => false,
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