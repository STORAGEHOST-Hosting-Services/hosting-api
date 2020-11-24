<?php

namespace Users;

use PDO;
use usersDeleteModel;

class Delete
{
    private PDO $pdo;
    private string $email;

    /**
     * delete constructor.
     * @param PDO $pdo
     * @param string $email
     */
    public function __construct(PDO $pdo, string $email)
    {
        $this->pdo = $pdo;
        $this->email = $email;
    }

    public function deleteUser()
    {
        return (new usersDeleteModel($this->pdo, $this->email))->deleteUser();
    }
}