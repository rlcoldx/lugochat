<?php

namespace Agencia\Close\Models\Home;

use Agencia\Close\Conn\Database\Database;
use Agencia\Close\Conn\Database\FeedbackDatabase;
use Agencia\Close\Conn\Table;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Helpers\String\Strings;
use Agencia\Close\Models\Model;


class Home extends Model 
{

    public function checkAgendamentos($company): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM agendamentos WHERE id_empresa = :id_empresa AND status_agendamento = 'Pendente' ORDER BY id DESC", "id_empresa={$company}");
        return $this->read;
    }

}