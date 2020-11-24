<?php
/** This file contains the required methods to receive and validate the form data for registering a user.
 * @author Cyril Buchs
 * @version 1.6
 */

namespace Users;

include "model/usersRegisterModel.php";

use PDO;
use Users\usersRegisterModel;

class Register
{
    private PDO $pdo;
    private usersRegisterModel $model;
    private array $form_data;
    private array $valid_form_data;

    /**
     * Register constructor.
     * @param array $form_data
     * @param PDO $pdo
     */
    public function __construct(array $form_data, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->form_data = $form_data;
        $this->valid_form_data = array();
    }

    /**
     * Method used to receive the data and validate it.
     * @return array|string
     */
    public function getFormData()
    {
        if (!empty($this->form_data)) {
            $this->form_data = [$this->form_data['lastname'], $this->form_data['firstname'], $this->form_data['email'], $this->form_data['address'], $this->form_data['city'], $this->form_data['zip'], $this->form_data['country'], $this->form_data['password'], $this->form_data['password_conf']];
            //var_dump($this->form_data);

            $result = $this->checkPassword();
            if (is_null($result)) {
                // No error occurred during password treatment, proceeding
                $result = $this->trim();
                //var_dump($result);
                if (is_array($result)) {
                    // No error occurred during trim, proceeding
                    $result_validation = $this->validateFormData($result);
                    //var_dump($result_validation);
                    if (is_array($result_validation)) {
                        // No error occurred during validation, proceeding by calling the model
                        $model = new usersRegisterModel($this->pdo, $this->valid_form_data);
                        if ($model->checkEmailExistence()) {
                            // User does not exist in the database, proceeding
                            $result_user_creation = $model->createUser();
                            if (is_array($result_user_creation)) {
                                return $result_user_creation;
                            } else {
                                return "user_creation_error";
                            }
                        } else {
                            return "user_already_exists";
                        }

                    } else {
                        return $result_validation;
                    }
                } else {
                    return $result;
                }
            } else {
                return $result;
            }
        } else {
            return "bad_post";
        }
    }

    private function checkPassword()
    {
        // Get the password and the password confirmation from the array and assign it to local var
        $password = $this->form_data[7];
        $password_conf = $this->form_data[8];

        // Compare the two strings
        if ($password == $password_conf) {
            $final_password = $password;

            $uppercase = preg_match('@[A-Z]@', $final_password);
            $lowercase = preg_match('@[a-z]@', $final_password);
            $number = preg_match('@[0-9]@', $final_password);
            //$specialChars = preg_match('@[^\w]@', $password);

            if ($uppercase && $lowercase && $number && strlen($final_password) >= 8) {
                // Password is valid
                //var_dump($this->form_data);

                // Add hashed password in the array
                $this->valid_form_data['password'] = password_hash($final_password, PASSWORD_DEFAULT);
            } else {
                return "password_not_meeting_requirements";
            }

        } else {
            // If password isn't the same as the confirmation, delete the array and print error
            return "bad_password";
        }
        return null;
    }

    private function trim()
    {
        // Trim all spaces
        if (!empty($this->form_data)) {
            $trimed_lastname = trim($this->form_data[0]);
            $trimed_firstname = trim($this->form_data[1]);
            $trimed_email = trim($this->form_data[2]);
            $trimed_address = trim($this->form_data[3]);
            $trimed_city = trim($this->form_data[4]);
            $trimed_zip = trim($this->form_data[5]);
            $trimed_country = trim($this->form_data[6]);
            $trimedFormData = array(
                'lastname' => $trimed_lastname,
                'firstname' => $trimed_firstname,
                'email' => $trimed_email,
                'address' => $trimed_address,
                'zip' => $trimed_zip,
                'city' => $trimed_city,
                'country' => $trimed_country
            );
        } else {
            return "bad_trim";
        }

        // Give an array of unwanted chars
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

        // Create a new array who will store the validated values
        $caseFormData = array();

        //var_dump($trimedFormData);

        // Lower firstname and lastname, and put first word in upper case
        $lastname = strtolower($trimedFormData['lastname']);
        $lastname = ucwords($lastname);

        // Clear the accentuation
        $lastname = strtr($lastname, $unwanted_array);
        $caseFormData[] = $lastname;

        $firstname = strtolower($trimedFormData['firstname']);
        $firstname = ucwords($firstname);

        // Clear the accentuation
        $firstname = strtr($firstname, $unwanted_array);
        $caseFormData[] = $firstname;

        // Lower email address (email cannot have any upper case letter)
        $email = $trimedFormData['email'];
        $email = strtolower($email);
        $caseFormData[] = $email;

        // Lower complete address (without ZIP)
        // Also clear comma(s) in address and city
        $address = $trimedFormData['address'];
        $address = str_replace(',', '', $address);
        $address = strtolower($address);
        $address = ucwords($address);
        $caseFormData[] = $address;

        // Add ZIP code in the array
        $caseFormData[] = $trimedFormData['zip'];

        // Add city in the array
        $city = $trimedFormData['city'];
        $city = str_replace(',', '', $city);
        $city = strtolower($city);
        $city = ucwords($city);
        $caseFormData[] = $city;

        // Add country in the array
        $caseFormData[] = $trimedFormData['country'];

        //var_dump($caseFormData);
        return $caseFormData;
    }

    private function validateFormData(array $caseFormData)
    {
        //var_dump($caseFormData);
        // Check if vars are empty
        if (empty($caseFormData) || empty($caseFormData[0]) || empty($caseFormData[1]) || empty($caseFormData[2]) || empty($caseFormData[3]) ||
            empty($caseFormData[4]) || empty($caseFormData[5]) || empty($caseFormData[6])) {
            return "error";
        }

        if (filter_var($caseFormData[0], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['lastname'] = preg_replace('/\d+/u', '', $caseFormData[0]);
        } else {
            return "bad_last_name";
        }

        if (filter_var($caseFormData[1], FILTER_SANITIZE_STRING)) {
            $this->valid_form_data['firstname'] = preg_replace('/\d+/u', '', $caseFormData[1]);
        } else {
            return "bad_first_name";
        }

        // Validate email
        if (filter_var($caseFormData[2], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL)) {
            $this->valid_form_data['email'] = $caseFormData[2];
        } else {
            return "bad_email";
        }

        // Validate address
        if (preg_match('/[A-Za-z0-9\-,.]+/', $caseFormData[3])) {
            $this->valid_form_data['address'] = $caseFormData[3];
        } else {
            return "bad_address";
        }

        // Validate zip code
        if (filter_var($caseFormData[4], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1000, "max_range" => 9999)))) {
            $this->valid_form_data['zip'] = $caseFormData[4];
        } else {
            return "bad_zip";
        }

        // Validate city
        $validCity = $caseFormData[5];
        if (filter_var($validCity, FILTER_SANITIZE_STRING)) {
            $validCity = preg_replace('/\d+/u', '', $validCity);
            $this->valid_form_data['city'] = $validCity;
        } else {
            return "bad_city";
        }

        // Validate country
        // Validate country
        if ($caseFormData[6] == "ch" || $caseFormData[6] == "fr") {
            $this->valid_form_data['country'] = $caseFormData[6];
        } else {
            return "bad_country";
        }

        //var_dump($this->valid_form_data);

        return $this->valid_form_data;
    }
}