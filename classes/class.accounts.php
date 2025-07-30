<?php


require_once 'class.db_connect.php';
require_once 'class.constant.php';



class accounts extends db_connect
{

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

  public function signUp($name, $email, $password) {
    $result = array(
        "error" => true,
        "error_code" => ERROR_UNKNOWN
    );

    try {
        $hashedPassword = $this->getHashedPassword($password);

        // Sanitize inputs (optional but good practice)
        $name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
        $email = strtolower(trim($email)); // Normalize email case

        $stmt = $this->db->prepare("
            INSERT INTO tbl_users (name, email, password, status)
            VALUES (:name, :email, :password, '1')
        ");

        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $user_id = $this->db->lastInsertId();

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "user_id" => $user_id
            );
        } else {
            // Optional: log error
            error_log("SignUp failed: " . implode(", ", $stmt->errorInfo()));
        }

    } catch (Exception $e) {
        error_log("Exception during signUp: " . $e->getMessage());
        $result['msg'] = "Internal server error.";
    }

    return $result;
}


    public function isUserAlreadyRegisteredByEmail($email)
    {

        $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;


    }

    public function login($email, $password) {
    $stmt = $this->db->prepare("SELECT * FROM tbl_users WHERE email = :email AND status = '1'");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['password'])) {
            return true; // Or return $row;
        }
    }

    return false;
}


    public function getAccountInfoByEmail($email)
    {

        $result = array();

        $stmt = $this->db->prepare("SELECT u.* from tbl_users as u where u.email=:email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "status" => $row['status']
            );

            return $result;

        }

        return $result;
    }

    public function getAccountInfoById($id)
    {

        $result = array();

        $stmt = $this->db->prepare("SELECT u.* from tbl_users as u where u.id=:id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email'],
                "status" => $row['status']
            );


            return $result;

        }

        return $result;
    }

    public function getHashedPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}