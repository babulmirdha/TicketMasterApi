<?php

require_once 'classes/class.constant.php';
require_once 'classes/class.accounts.php';
require_once 'classes/class.helper.php';


if (!empty($_POST)) {

    $name = isset($_POST['name']) ? $_POST['name'] : '';

    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $password = isset($_POST['password']) ? $_POST['password'] : '';


    // Validation
    if (empty($name) || empty($email)  || empty($password)) {
        echo json_encode(array('error' => TRUE, 'msg' => 'All fields are required.'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('error' => TRUE, 'msg' => 'Invalid email format.'));
        exit;
    }


    if (strlen($password) < 8) {
        echo json_encode(array('error' => TRUE, 'msg' => 'Password must be at least 8 characters long.'));
        exit;
    }

    $account = new accounts();

    //Checking if user is already registered
    if ($account->isUserAlreadyRegisteredByEmail($email)) echo json_encode(array('error' => TRUE, 'msg' => 'User already registered with this email.'));
    else {

        $result = $account->signUp($name,$email,$password);

        if ($result['error'] == False) {

            echo json_encode(array('error' => False, 'msg' => 'User Successfully Registered!', "data" => $account->getAccountInfoByEmail($email)));
        } else echo json_encode(array('error' => TRUE, 'msg' => 'Unable to Sign Up!'));


    }

}
else{
    echo json_encode(array('error' => TRUE, 'msg' => 'Invalid Request.'));
}


?>