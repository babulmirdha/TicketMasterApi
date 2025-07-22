<?php


require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';


if (!empty($_POST)) {


    $userId= isset($_POST['userId']) ? $_POST['userId'] : 0;


    //Getting My Events
    $events = new events();
    $events->setRequesterId($userId);
    $result=$events->getUserEventsWithTickets();

    echo json_encode($result);
    exit;

}





?>