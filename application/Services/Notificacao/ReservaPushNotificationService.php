<?php

namespace Agencia\Close\Services\Notificacao;

use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Notificacao\NotificacaoModel;
use Agencia\Close\Models\Reserva\Reserva;

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
    /**
     * Processa fila: pagamento approved + notificao = no → push → notificao = yes.
     */
    public function processarReservasPagasPendentes(): int
    {
        $reservaModel = new Reserva();
        $rows = $reservaModel->getReservasPagasSemNotificacao()->getResult() ?: [];
        $enviadas = 0;

        foreach ($rows as $row) {
            $idReserva = (int) ($row['id'] ?? 0);
            if ($idReserva <= 0) {
                continue;
            }

            $resultado = $this->enviarNotificacaoPagamento($idReserva);
            if ($resultado['enviado'] && $reservaModel->marcarNotificaoEnviada($idReserva)) {
                $enviadas++;
            }
        }

        return $enviadas;
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

            if (!$apiResponse['ok']) {
                return [
                    'enviado' => false,
                    'message' => $apiResponse['message'],
                    'destinatarios' => count($keys),
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
