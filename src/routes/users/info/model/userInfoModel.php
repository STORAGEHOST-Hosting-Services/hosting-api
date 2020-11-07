<?php


class userInfoModel
{
    public function listContainers(int $id, PDO $pdo)
    {
        // Search for containers using the user ID
        $req = $pdo->prepare('SELECT * FROM hosting.containers WHERE user_id = :id');
        $req->bindParam(':id', $id);
        $req->execute();

        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listVms(int $id, PDO $pdo)
    {
        // Search for containers using the user ID
        $req = $pdo->prepare('SELECT * FROM hosting.vms WHERE user_id = :id');
        $req->bindParam(':id', $id);
        $req->execute();

        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}