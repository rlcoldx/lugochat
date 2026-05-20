<?php

namespace Agencia\Close\Services\Notificacao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Notificacao\NotificacaoModel;

class ReservaPushNotificationService
{
    private NotificacaoModel $notificacaoModel;
    private OneSignalService $oneSignal;

    public function __construct()
    {
        $this->notificacaoModel = new NotificacaoModel();
        $this->oneSignal = new OneSignalService();
    }

    /**
     * Notifica apenas quando o pagamento estiver approved.
     * Admin (tipo 0): qualquer motel. Motel (tipo 2) e equipe (tipo 3): só o id_motel da reserva.
     */
    public function notificarPagamentoAprovado(int $idReserva): void
    {
        $this->enviarNotificacaoPagamento($idReserva);
    }

    /**
     * Teste provisório: notifica a última reserva com pagamento approved.
     *
     * @return array{status: string, message: string, id_reserva?: int, codigo_reserva?: string, destinatarios?: int}
     */
    public function testarUltimaReservaPaga(): array
    {
        $reserva = $this->notificacaoModel->getUltimaReservaPaga();
        if (!$reserva) {
            return [
                'status' => 'error',
                'message' => 'Nenhuma reserva com pagamento aprovado encontrada.',
            ];
        }

        $idReserva = (int) $reserva['id'];
        $resultado = $this->enviarNotificacaoPagamento($idReserva);

        if (!$resultado['enviado']) {
            return [
                'status' => 'error',
                'message' => $resultado['message'],
                'id_reserva' => $idReserva,
                'codigo_reserva' => $reserva['codigo_reserva'] ?? null,
            ];
        }

        return [
            'status' => 'success',
            'message' => sprintf(
                'Push enviado para %d dispositivo(s). Reserva %s (#%d).',
                $resultado['destinatarios'],
                $reserva['codigo_reserva'] ?? $idReserva,
                $idReserva
            ),
            'id_reserva' => $idReserva,
            'codigo_reserva' => $reserva['codigo_reserva'] ?? null,
            'destinatarios' => $resultado['destinatarios'],
        ];
    }

    /**
     * @return array{enviado: bool, message: string, destinatarios: int}
     */
    private function enviarNotificacaoPagamento(int $idReserva): array
    {
        try {
            $reserva = $this->getReservaDados($idReserva);
            if (!$reserva || ($reserva['pagamento_status'] ?? '') !== 'approved') {
                return [
                    'enviado' => false,
                    'message' => 'Reserva sem pagamento aprovado.',
                    'destinatarios' => 0,
                ];
            }

            $idMotel = (int) ($reserva['id_motel'] ?? 0);
            if ($idMotel <= 0) {
                return [
                    'enviado' => false,
                    'message' => 'Motel da reserva inválido.',
                    'destinatarios' => 0,
                ];
            }

            $keys = $this->notificacaoModel->getPushKeysPagamentoAprovado($idMotel);
            if (empty($keys)) {
                return [
                    'enviado' => false,
                    'message' => 'Nenhum usuário com pushKey ativo (admin, motel ou equipe).',
                    'destinatarios' => 0,
                ];
            }

            $valor = $reserva['valor_reserva'] ?? $reserva['pagamento_valor'] ?? '';
            $valorFmt = $valor !== '' ? ' — R$ ' . number_format((float) $valor, 2, ',', '.') : '';
            $motelNome = $reserva['motel_nome'] ?? 'Motel';
            $codigo = $reserva['codigo_reserva'] ?? (string) $idReserva;

            $apiResponse = $this->oneSignal->send(
                $keys,
                'Pagamento confirmado',
                sprintf('Reserva %s — %s%s. Pagamento aprovado.', $codigo, $motelNome, $valorFmt),
                $this->urlReserva($idReserva)
            );

            if ($apiResponse === null) {
                return [
                    'enviado' => false,
                    'message' => 'Falha ao enviar via OneSignal API.',
                    'destinatarios' => 0,
                ];
            }

            return [
                'enviado' => true,
                'message' => 'Enviado.',
                'destinatarios' => count($keys),
            ];
        } catch (\Throwable $e) {
            error_log('Push pagamento aprovado: ' . $e->getMessage());
            return [
                'enviado' => false,
                'message' => 'Erro interno ao enviar push.',
                'destinatarios' => 0,
            ];
        }
    }

    private function getReservaDados(int $idReserva): ?array
    {
        $read = new Read();
        $read->FullRead(
            "SELECT r.*, p.pagamento_status, p.pagamento_valor, u.nome AS motel_nome
             FROM reservas AS r
             LEFT JOIN pagamentos AS p ON p.id_reserva = r.id
             LEFT JOIN usuarios AS u ON u.id = r.id_motel AND u.tipo = '2'
             WHERE r.id = :id
             LIMIT 1",
            "id={$idReserva}"
        );

        $row = $read->getResultSingle();
        return $row ?: null;
    }

    private function urlReserva(int $idReserva): string
    {
        return rtrim(DOMAIN, '/') . '/reserva/' . $idReserva;
    }
}
