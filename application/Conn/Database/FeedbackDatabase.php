<?php

namespace Agencia\Close\Conn\Database;

class FeedbackDatabase extends  Database
{
    protected function setDatabaseInfo(): void
    {
        $this->host = HOST_FEEDBACK;
        $this->user = USER_FEEDBACK;
        $this->password = PASS_FEEDBACK;
        $this->database = DBSA_FEEDBACK;
    }
}