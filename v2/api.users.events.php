<?php


require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';


if (!empty($_POST)) {


    $user_id= isset($_POST['user_id']) ? $_POST['user_id'] : 0;


    //Getting My Events
    $events = new events();
    $events->setRequesterId($user_id);
    $result=$events->getUserEventsWithTickets();

    echo json_encode($result);
    exit;

}





?>