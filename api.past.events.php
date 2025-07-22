<?php

require_once 'classes/class.constant.php';
require_once 'classes/class.events.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $events = new events();

    $result = $events->getPastEvents();

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

echo json_encode([
    "error" => true,
    "error_code" => 400,
    "msg" => "Invalid request method. Use GET."
]);
exit;
