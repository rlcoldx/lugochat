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
        try {
            $reserva = $this->getReservaDados($idReserva);
            if (!$reserva || ($reserva['pagamento_status'] ?? '') !== 'approved') {
                return;
            }

            $idMotel = (int) ($reserva['id_motel'] ?? 0);
            if ($idMotel <= 0) {
                return;
            }

            $keys = $this->notificacaoModel->getPushKeysPagamentoAprovado($idMotel);
            if (empty($keys)) {
                return;
            }

            $valor = $reserva['valor_reserva'] ?? $reserva['pagamento_valor'] ?? '';
            $valorFmt = $valor !== '' ? ' — R$ ' . number_format((float) $valor, 2, ',', '.') : '';
            $motelNome = $reserva['motel_nome'] ?? 'Motel';
            $codigo = $reserva['codigo_reserva'] ?? (string) $idReserva;

            $this->oneSignal->send(
                $keys,
                'Pagamento confirmado',
                sprintf('Reserva %s — %s%s. Pagamento aprovado.', $codigo, $motelNome, $valorFmt),
                $this->urlReserva($idReserva)
            );
        } catch (\Throwable $e) {
            error_log('Push pagamento aprovado: ' . $e->getMessage());
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
