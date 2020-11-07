<?php

namespace Users;

require "model/userInfoModel.php";

class Info
{
    private $id;
    private $pdo;

    public function __construct(int $id, \PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;
    }

    public function listContainers()
    {
        return (new \userInfoModel())->listContainers($this->id, $this->pdo);
    }

    public function listVms()
    {
        return (new \userInfoModel())->listVms($this->id, $this->pdo);
    }
}