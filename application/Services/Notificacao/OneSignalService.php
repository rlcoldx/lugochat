<?php

namespace Agencia\Close\Services\Notificacao;

class OneSignalService
{
    /**
     * @param array<int, string> $subscriptionIds
     * @return array{ok: bool, message: string, http_code: int, notification_id: ?string, errors: array}
     */
    public function send(array $subscriptionIds, string $titulo, string $mensagem, ?string $url = null): array
    {
        $subscriptionIds = array_values(array_filter(array_unique($subscriptionIds), static function ($id) {
            return is_string($id) && $id !== '' && $id !== 'null';
        }));

        if (empty($subscriptionIds)) {
            return [
                'ok' => false,
                'message' => 'Nenhum subscription ID informado.',
                'http_code' => 0,
                'notification_id' => null,
                'errors' => [],
            ];
        }

        if (!defined('ONESIGNAL_APP_ID') || !defined('ONESIGNAL_REST_API_KEY')) {
            return [
                'ok' => false,
                'message' => 'Credenciais OneSignal não configuradas.',
                'http_code' => 0,
                'notification_id' => null,
                'errors' => [],
            ];
        }

        $payload = [
            'app_id' => ONESIGNAL_APP_ID,
            'target_channel' => 'push',
            'contents' => ['en' => $mensagem],
            'headings' => ['en' => $titulo],
            'include_subscription_ids' => $subscriptionIds,
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        $jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            return [
                'ok' => false,
                'message' => 'Erro ao montar JSON da notificação.',
                'http_code' => 0,
                'notification_id' => null,
                'errors' => [],
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.onesignal.com/notifications',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Key ' . ONESIGNAL_REST_API_KEY,
            ],
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            error_log('OneSignal cURL: ' . $curlError);
            return [
                'ok' => false,
                'message' => 'Erro de conexão com OneSignal: ' . $curlError,
                'http_code' => $httpCode,
                'notification_id' => null,
                'errors' => [],
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            error_log('OneSignal HTTP ' . $httpCode . ' (resposta inválida): ' . $response);
            return [
                'ok' => false,
                'message' => 'Resposta inválida da OneSignal (HTTP ' . $httpCode . ').',
                'http_code' => $httpCode,
                'notification_id' => null,
                'errors' => [],
            ];
        }

        if ($httpCode >= 400) {
            $message = $this->formatarErrosApi($decoded) ?: ('HTTP ' . $httpCode);
            error_log('OneSignal HTTP ' . $httpCode . ': ' . $response);
            return [
                'ok' => false,
                'message' => $message,
                'http_code' => $httpCode,
                'notification_id' => null,
                'errors' => $decoded['errors'] ?? [],
            ];
        }

        if (!empty($decoded['id'])) {
            return [
                'ok' => true,
                'message' => 'Notificação criada.',
                'http_code' => $httpCode,
                'notification_id' => (string) $decoded['id'],
                'errors' => [],
            ];
        }

        $message = $this->formatarErrosApi($decoded);
        if ($message === '') {
            $message = 'OneSignal não criou a mensagem: nenhum pushKey válido/inscrito. '
                . 'Abra o painel, clique em "Ativar notificações" e tente de novo.';
        }

        error_log('OneSignal sem id (HTTP ' . $httpCode . '): ' . $response);

        return [
            'ok' => false,
            'message' => $message,
            'http_code' => $httpCode,
            'notification_id' => null,
            'errors' => $decoded['errors'] ?? [],
        ];
    }

    private function formatarErrosApi(array $decoded): string
    {
        $partes = [];

        if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
            foreach ($decoded['errors'] as $chave => $valor) {
                if (is_array($valor)) {
                    $partes[] = $chave . ': ' . implode(', ', array_slice($valor, 0, 3));
                } else {
                    $partes[] = (string) $valor;
                }
            }
        }

        if (!empty($decoded['error'])) {
            $partes[] = is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
        }

        return implode(' | ', $partes);
    }
}
