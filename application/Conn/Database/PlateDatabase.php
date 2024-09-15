<?php

namespace Agencia\Close\Conn\Database;

class PlateDatabase extends  Database
{
    protected function setDatabaseInfo(): void
    {
        $this->host = HOST_PLATE;
        $this->user = USER_PLATE;
        $this->password = PASS_PLATE;
        $this->database = DBSA_PLATE;
    }
}