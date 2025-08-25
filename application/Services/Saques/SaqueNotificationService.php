<?php

namespace Agencia\Close\Services\Saques;

use Agencia\Close\Adapters\EmailAdapter;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Models\Saques\SaquesPainel;

class SaqueNotificationService
{
    private EmailAdapter $emailAdapter;
    private Moteis $moteisModel;
    private SaquesPainel $saquesModel;

    public function __construct()
    {
        $this->emailAdapter = new EmailAdapter();
        $this->moteisModel = new Moteis();
        $this->saquesModel = new SaquesPainel();
    }

    /**
     * Envia email de notificação para o admin quando um novo saque é criado
     * 
     * @param int $idSaque ID do saque criado
     * @param int $idMotel ID do motel
     * @return bool
     */
    public function notificarNovoSaque(int $idSaque, int $idMotel): bool
    {
        try {
            // Buscar dados do saque
            $saque = $this->getDadosSaque($idSaque, $idMotel);
            if (!$saque) {
                return false;
            }

            // Buscar dados do motel
            $motel = $this->getDadosMotel($idMotel);
            if (!$motel) {
                return false;
            }

            // Buscar dados da conta bancária
            $conta = $this->getDadosContaBancaria($saque['id_conta_bancaria'], $idMotel);
            if (!$conta) {
                return false;
            }

            // Preparar dados para o template
            $dadosEmail = [
                'saque' => $saque,
                'motel' => $motel,
                'conta' => $conta
            ];

            // Configurar e enviar email
            $this->emailAdapter->addAddress(MAIL_ADMIN);
            $this->emailAdapter->setSubject('💰 Novo Pedido de Saque - ' . $motel['nome']);
            $this->emailAdapter->setBody('components/emails/saque-notification.twig', $dadosEmail);
            
            $this->emailAdapter->send('Email de notificação enviado com sucesso!');
            
            return !$this->emailAdapter->getResult()->getError();
            
        } catch (\Exception $e) {
            // Log do erro (pode ser implementado conforme necessário)
            error_log("Erro ao enviar email de notificação de saque: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os dados completos do saque
     * 
     * @param int $idSaque
     * @param int $idMotel
     * @return array|null
     */
    private function getDadosSaque(int $idSaque, int $idMotel): ?array
    {
        $read = new \Agencia\Close\Conn\Read();
        $read->FullRead(
            "SELECT s.*, c.banco_pix, c.conta_ag, c.conta_numero, c.conta_tipo, 
                    c.conta_responsavel, c.conta_cpf_cnpj
             FROM saques AS s 
             INNER JOIN contas_bancarias AS c ON c.id = s.id_conta_bancaria
             WHERE s.id = :id_saque AND s.id_motel = :id_motel",
            "id_saque={$idSaque}&id_motel={$idMotel}"
        );

        if ($read->getResult()) {
            return $read->getResultSingle();
        }

        return null;
    }

    /**
     * Busca os dados do motel
     * 
     * @param int $idMotel
     * @return array|null
     */
    private function getDadosMotel(int $idMotel): ?array
    {
        $result = $this->moteisModel->getMotel($idMotel);
        
        if ($result->getResult()) {
            return $result->getResultSingle();
        }

        return null;
    }

    /**
     * Busca os dados da conta bancária
     * 
     * @param int $idConta
     * @param int $idMotel
     * @return array|null
     */
    private function getDadosContaBancaria(int $idConta, int $idMotel): ?array
    {
        $result = $this->saquesModel->getContaID($idMotel, $idConta);
        
        if ($result->getResult()) {
            return $result->getResultSingle();
        }

        return null;
    }

    /**
     * Envia notificação para múltiplos saques (útil para notificações em lote)
     * 
     * @param array $idsSaques Array com IDs dos saques
     * @param int $idMotel ID do motel
     * @return array Array com resultados de cada envio
     */
    public function notificarMultiplosSaques(array $idsSaques, int $idMotel): array
    {
        $resultados = [];
        
        foreach ($idsSaques as $idSaque) {
            $resultados[$idSaque] = $this->notificarNovoSaque($idSaque, $idMotel);
        }
        
        return $resultados;
    }
}
