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
            return $this->falha('Nenhum subscription ID informado.');
        }

        if (!defined('ONESIGNAL_APP_ID') || !defined('ONESIGNAL_REST_API_KEY')) {
            return $this->falha('Credenciais OneSignal não configuradas.');
        }

        $apiKey = trim(ONESIGNAL_REST_API_KEY);
        if ($apiKey === '') {
            return $this->falha('ONESIGNAL_REST_API_KEY vazio no config.php do servidor.');
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
            return $this->falha('Erro ao montar JSON da notificação.');
        }

        $authHeaders = $this->montarHeadersAutenticacao($apiKey);
        $ultimaResposta = null;

        foreach ($authHeaders as $authHeader) {
            $ultimaResposta = $this->executarRequest($jsonData, $authHeader);
            if ($ultimaResposta['auth_ok']) {
                break;
            }
        }

        if ($ultimaResposta === null) {
            return $this->falha('Não foi possível contactar a API OneSignal.');
        }

        return $this->interpretarResposta($ultimaResposta);
    }

    /**
     * @return array<int, string>
     */
    private function montarHeadersAutenticacao(string $apiKey): array
    {
        $headers = [
            'key ' . $apiKey,
            'Key ' . $apiKey,
            'Basic ' . base64_encode($apiKey . ':'),
        ];

        return array_values(array_unique($headers));
    }

    /**
     * @return array{auth_ok: bool, http_code: int, body: string, curl_error: string}
     */
    private function executarRequest(string $jsonData, string $authorizationValue): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.onesignal.com/notifications',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: ' . $authorizationValue,
            ],
        ]);

        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false) {
            error_log('OneSignal cURL: ' . $curlError);
            return [
                'auth_ok' => false,
                'http_code' => $httpCode,
                'body' => '',
                'curl_error' => $curlError,
            ];
        }

        $authOk = $httpCode < 400 && stripos($body, 'Access denied') === false;

        return [
            'auth_ok' => $authOk,
            'http_code' => $httpCode,
            'body' => (string) $body,
            'curl_error' => $curlError,
        ];
    }

    /**
     * @param array{auth_ok: bool, http_code: int, body: string, curl_error: string} $resposta
     * @return array{ok: bool, message: string, http_code: int, notification_id: ?string, errors: array}
     */
    private function interpretarResposta(array $resposta): array
    {
        if ($resposta['body'] === '' && $resposta['http_code'] === 0) {
            return $this->falha(
                'Erro de conexão com OneSignal: ' . ($resposta['curl_error'] ?: 'sem resposta'),
                $resposta['http_code']
            );
        }

        $decoded = json_decode($resposta['body'], true);
        if (!is_array($decoded)) {
            error_log('OneSignal HTTP ' . $resposta['http_code'] . ' (resposta inválida): ' . $resposta['body']);
            return $this->falha(
                'Resposta inválida da OneSignal (HTTP ' . $resposta['http_code'] . ').',
                $resposta['http_code']
            );
        }

        if ($resposta['http_code'] >= 400 || stripos($resposta['body'], 'Access denied') !== false) {
            $message = $this->formatarErrosApi($decoded) ?: ('HTTP ' . $resposta['http_code']);
            if (stripos($message, 'Access denied') !== false || stripos($message, 'API key') !== false) {
                $message .= ' Gere uma nova App API Key em OneSignal → Settings → Keys & IDs e atualize ONESIGNAL_REST_API_KEY no config.php do servidor.';
            }
            error_log('OneSignal HTTP ' . $resposta['http_code'] . ': ' . $resposta['body']);
            return [
                'ok' => false,
                'message' => $message,
                'http_code' => $resposta['http_code'],
                'notification_id' => null,
                'errors' => $decoded['errors'] ?? [],
            ];
        }

        if (!empty($decoded['id'])) {
            return [
                'ok' => true,
                'message' => 'Notificação criada.',
                'http_code' => $resposta['http_code'],
                'notification_id' => (string) $decoded['id'],
                'errors' => [],
            ];
        }

        $message = $this->formatarErrosApi($decoded);
        if ($message === '') {
            $message = 'OneSignal não criou a mensagem: nenhum pushKey válido/inscrito. '
                . 'Clique em "Ativar notificações" no painel e teste de novo.';
        }

        error_log('OneSignal sem id (HTTP ' . $resposta['http_code'] . '): ' . $resposta['body']);

        return [
            'ok' => false,
            'message' => $message,
            'http_code' => $resposta['http_code'],
            'notification_id' => null,
            'errors' => $decoded['errors'] ?? [],
        ];
    }

    /**
     * @return array{ok: bool, message: string, http_code: int, notification_id: ?string, errors: array}
     */
    private function falha(string $message, int $httpCode = 0): array
    {
        return [
            'ok' => false,
            'message' => $message,
            'http_code' => $httpCode,
            'notification_id' => null,
            'errors' => [],
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
