<?php

namespace Agencia\Close\Models\Banners;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class Banners extends Model
{
	public function getImagens($local): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM banners WHERE `local` = :local ORDER BY `order`, `id` ASC", "local={$local}");
        return $this->read;
    }

    public function getLink($id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM banners WHERE id = :id", "id={$id}");
        return $this->read;
    }

    public function saveLink($params): Read
    {
        $this->read = new Read();
        $this->read->FullRead("UPDATE `banners` SET `link` = :link WHERE nome = :nome", "link={$params['link']}&nome={$params['nome']}");
        return $this->read;
    }
}