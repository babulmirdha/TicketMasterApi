<?php


require_once 'class.db_connect.php';
require_once 'class.constant.php';



class accounts extends db_connect
{

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function signUp($name,$email,$password){

        $result = array("error" => true,
            "error_code" => ERROR_UNKNOWN);

        $hashedPassword = $this->getHashedPassword($password);

        $time = time();

        $stmt = $this->db->prepare("INSERT INTO tbl_users values ('', :name, :email, :password,'1')");
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $userId = $this->db->lastInsertId();


            $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS,
                "userId"=>$userId);

        }

        return $result;



    }

    public function isUserAlreadyRegisteredByEmail($email) {

        $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;


    }

    public function login($email,$password){

        $stmt = $this->db->prepare("SELECT * from tbl_users where email=:email AND status='1' ");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password']))
                return true;
            else false;
        }

        return false;
    }

    public function getAccountInfoByEmail($email)
    {

        $result=array();

        $stmt = $this->db->prepare("SELECT u.* from tbl_users as u where u.email=:email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result=array(
                "id"=>$row['id'],
                "name"=>$row['name'],
                "email"=>$row['email'],
                "status"=>$row['status']
            );

            return $result;

        }

        return $result;
    }

    public function getAccountInfoById($id)
    {

        $result=array();

        $stmt = $this->db->prepare("SELECT u.* from tbl_users as u where u.id=:id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result=array(
                "id"=>$row['id'],
                "name"=>$row['name'],
                "email"=>$row['email'],
                "status"=>$row['status']
            );


            return $result;

        }

        return $result;
    }

    public function getHashedPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}