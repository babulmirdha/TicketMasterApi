<?php

require_once 'classes/class.constant.php';
require_once 'classes/class.events.php';

// You should have DB connection setup inside your class.events.php constructor or somewhere globally.
// If not, initialize it here and pass it to events class constructor.

if (!empty($_POST)) {

    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

    // Initialize events class - add DB connection here if needed
    $events = new events();
    $events->setRequesterId($userId);

    // Fetch favourite events
    $result = $events->getFavouriteEventsForUser();

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
