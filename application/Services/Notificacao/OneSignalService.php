<?php

namespace Agencia\Close\Services\Notificacao;

class OneSignalService
{
    /**
     * Envia push para uma lista de subscription IDs (OneSignal v16).
     *
     * @param array<int, string> $subscriptionIds
     */
    public function send(array $subscriptionIds, string $titulo, string $mensagem, ?string $url = null): ?array
    {
        $subscriptionIds = array_values(array_filter(array_unique($subscriptionIds), static function ($id) {
            return is_string($id) && $id !== '' && $id !== 'null';
        }));

        if (empty($subscriptionIds)) {
            return null;
        }

        if (!defined('ONESIGNAL_APP_ID') || !defined('ONESIGNAL_REST_API_KEY')) {
            error_log('OneSignal: credenciais não definidas em config.php');
            return null;
        }

        $payload = [
            'app_id' => ONESIGNAL_APP_ID,
            'contents' => ['en' => $mensagem, 'pt' => $mensagem],
            'headings' => ['en' => $titulo, 'pt' => $titulo],
            'include_subscription_ids' => $subscriptionIds,
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        $jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.onesignal.com/notifications',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Key ' . ONESIGNAL_REST_API_KEY,
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $httpCode >= 400) {
            error_log('OneSignal HTTP ' . $httpCode . ': ' . (string) $response);
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}
