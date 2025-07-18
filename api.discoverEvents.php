<?php


require_once 'classes/class.constant.php';
require_once 'classes/class.events.php';


if (!empty($_POST)) {


    $userId= isset($_POST['userId']) ? $_POST['userId'] : 0;

    $searchText= isset($_POST['searchText']) ? $_POST['searchText'] : '';


    //Searching Events
    $events = new events();
    $events->setRequesterId($userId);
    $result=$events->searchEvents($searchText);

    echo json_encode($result);
    exit;

}





?>