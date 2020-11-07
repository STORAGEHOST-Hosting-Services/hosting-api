<?php

namespace Vms;

class vmsInfoModel
{
    public function listVms(int $id, \PDO $pdo)
    {
        $req = $pdo->prepare('SELECT * FROM hosting.vms LEFT JOIN "system" ON hosting.vms.id = hosting."system".id WHERE "vms".id = :id');
        $req->bindParam(':id', $id);
        $req->execute();

        return $req->fetchAll(\PDO::FETCH_ASSOC);
    }
}