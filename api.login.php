<?php

require_once 'classes/class.constant.php';

require_once 'classes/class.accounts.php';

if (!empty($_POST)) {

    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $account = new accounts();

    //Checking if user is already registered
    if ($account->login($email,$password)) $result = array('error' => False, 'msg' => 'User Login Successfully!',"data"=>$account->getAccountInfoByEmail($email));
    else  $result = array("error" => True, "msg" => "Incorrect Login Details!");
    echo json_encode($result);
    exit;

}
?>