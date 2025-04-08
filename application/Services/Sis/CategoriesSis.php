<?php

namespace Agencia\Close\Services\Sis;

use Agencia\Close\Conn\Read;

class CategoriesSis
{
    public function listCategories($token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => SIS_API.'/api/categories',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'token: '.$token,
            'softhouse: '.SOFTHOUSE
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        // Converte JSON em array
        $data = json_decode($response, true);

        return $data;
    }
}