<?php


require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';


if (!empty($_POST)) {


    $user_id= isset($_POST['user_id']) ? $_POST['user_id'] : 0;

    $searchText= isset($_POST['searchText']) ? $_POST['searchText'] : '';


    //Searching Events
    $events = new events();
    $events->setRequesterId($user_id);
    $result=$events->searchEvents($searchText);

    echo json_encode($result);
    exit;

}





?>