<?php

/**
 * This file contains the required functions to insert valid form data in the database.
 * @author Cyril Buchs
 * @version 1.7
 */

namespace Users;

use Config;
use PDO;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

include "C:\Users\Cyril\Documents\ProgrammingStuff\hosting-api\src\config\PHPMailer\src\Exception.php";
include "C:\Users\Cyril\Documents\ProgrammingStuff\hosting-api\src\config\PHPMailer\src\SMTP.php";
include "C:\Users\Cyril\Documents\ProgrammingStuff\hosting-api\src\config\PHPMailer\src\PHPMailer.php";
include "C:\Users\Cyril\Documents\ProgrammingStuff\hosting-api\src\config\Config.php";

$activation_key = md5(microtime(TRUE) * 100000);

class usersRegisterModel
{
    protected PDO $pdo;
    protected array $form_data;

    public function __construct(PDO $pdo, array $valid_form_data)
    {
        $this->pdo = $pdo;
        $this->form_data = $valid_form_data;
    }

    /**
     * Method to check if mail given by the user already exists in the database. If so, it will return an error message.
     * @return bool|null
     */
    public function checkEmailExistence()
    {
        $email = $this->form_data['email'];
        try {
            $req = $this->pdo->prepare('SELECT email FROM hosting.user WHERE email = :email');
            $req->bindParam(':email', $email);
            $req->execute();

            // Check if request rows are higher than 0. If yes, it means that the email exists
            if ($req->rowCount() > 0) {
                return false;
            } else {
                // Email does not exist, so account creating is OK
                return true;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return null;
    }

    /**
     * Method used to insert data in the DB.
     * @return array|bool
     */
    public function createUser()
    {
        global $activation_key;
        try {
            $req = $this->pdo->prepare('INSERT INTO hosting.user(lastname, firstname, email, address, city, zip, password, number_of_container, number_of_vm, activation, activation_key) VALUES (:lastname, :firstname, :email, :address, :city, :zip, :password, :number_of_container, :number_of_vm, :activation, :activation_key)');
            //'INSERT INTO tip.user(lastname, firstname, email, sex, address, city, postal_code, password, sub_status, activation_key, activation, token, token_login) VALUES (:lastname, :firstname, :email, :sex, :address, :city, :postal_code, :password, :sub_status, :activation_key, :activation, :token, :token_login)'
            $req->execute(
                array(
                    ':lastname' => $this->form_data['lastname'],
                    ':firstname' => $this->form_data['firstname'],
                    ':email' => $this->form_data['email'],
                    ':address' => $this->form_data['address'],
                    ':city' => $this->form_data['city'],
                    ':zip' => $this->form_data['zip'],
                    ':password' => $this->form_data['password'],
                    ':number_of_container' => 0,
                    ':number_of_vm' => 0,

                    // Set a default account status at 0, who means account disabled
                    ':activation' => 0,

                    ':activation_key' => $activation_key
                )
            );
            if ($req) {
                $this->sendMail($activation_key);
                $payload = [];
                array_push($payload, array(
                    "status" => "success",
                    'user_id' => $this->pdo->lastInsertId(),
                    'lastname' => $this->form_data['lastname'],
                    'firstname' => $this->form_data['firstname'],
                    'email' => $this->form_data['email'],
                    'address' => $this->form_data['address'],
                    'city' => $this->form_data['city'],
                    'zip' => $this->form_data['zip']
                ));
                return $payload;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
        return null;
    }

    /**
     * Method that will send an email to user when the insert pass is successful.
     * @param string $activation_key
     */
    private function sendMail(string $activation_key)
    {
        // Define some local vars
        $email = $this->form_data['email'];

        // Encode the email and the key as URL
        $encoded_email = urlencode($email);
        $encoded_key = urlencode($activation_key);

        // Set subject and body
        $subject = "Création de votre compte";
        $message = "Bonjour,<br/>
        Nous vous confirmons la réception de votre enregistrement sur le site Web de STORAGEHOST - Hosting Services.<br/><br/>
        <b>Votre compte requiert une activation.</b><br/><br/>
	    Merci de bien vouloir cliquer sur ce lien ou de le copier/coller dans un navigateur afin de l'activer :
	    <br/><br/>
        http://localhost/api/user/activation/email=" . $encoded_email . "&token=" . $encoded_key . "
        <br/>
        <br/>       
        ---------------<br/>
        Cet e-mail est généré automatiquement, merci de ne pas y répondre.<br/>
        En cas de besoin, contactez l'administrateur à admin@storagehost.ch
        ";

        // Create new PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Define server settings
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = Config::MAIL_SERVER;
            $mail->SMTPAuth = true;
            $mail->Username = Config::MAIL_USERNAME;
            $mail->Password = Config::MAIL_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            /**$mail->SMTPOptions = array(
             * 'ssl' => array(
             * 'verify_peer' => false,
             * 'verify_peer_name' => false,
             * 'allow_self_signed' => true
             * )
             * );*/

            // Define sender and recipients settings
            $mail->setFrom(Config::MAIL_USERNAME, 'STORAGEHOST - Hosting Services');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
        }
    }

}