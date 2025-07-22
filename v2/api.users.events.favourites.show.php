<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

    header('Content-Type: application/json');

    if ($userId <= 0) {
        echo json_encode([
            "error" => true,
            "error_code" => 400,
            "message" => "Invalid or missing userId"
        ]);
        exit;
    }

    $events = new events();
    $events->setRequesterId($userId);

    $result = $events->getFavouriteEventsForUser();

    echo json_encode($result);
    exit;
}

// If accessed by GET or other methods, return error
header('Content-Type: application/json');
echo json_encode([
    "error" => true,
    "error_code" => 405,
    "message" => "Method Not Allowed. Use POST."
]);
exit;
