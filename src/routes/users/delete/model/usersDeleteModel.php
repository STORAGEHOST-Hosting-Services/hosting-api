<?php


class usersDeleteModel
{
    private PDO $pdo;
    private string $email;

    public function __construct(PDO $pdo, string $email)
    {
        $this->pdo = $pdo;
        $this->email = $email;
    }

    /**
     * Method used to delete a user from the DB.
     * @return bool|string
     */
    public function deleteUser()
    {
        try {
            $req = $this->pdo->prepare("DELETE FROM hosting.user WHERE email = :email");
            $req->bindParam(':email', $this->email);
            $req->execute();

            if ($req) {
                return "ok";
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return json_encode($e->getMessage());
        }
    }
}