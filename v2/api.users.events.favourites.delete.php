<?php

require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get userId and eventId from POST
    $userId  = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $eventId = isset($_POST['eventId']) ? intval($_POST['eventId']) : 0;

    // Validate input
    if ($userId <= 0 || $eventId <= 0) {
        echo json_encode([
            "error" => true,
            "error_code" => 400,
            "message" => "Invalid userId or eventId."
        ]);
        exit;
    }

    // Initialize the events class
    $events = new events(); // assumes DB connection is handled in constructor

    // Perform removal
    $result = $events->removeFavouriteEvent($userId, $eventId);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
