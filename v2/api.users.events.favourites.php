<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    header('Content-Type: application/json');

    if ($user_id <= 0) {
        echo json_encode([
            "error" => true,
            "error_code" => 400,
            "message" => "Invalid or missing user_id"
        ]);
        exit;
    }

    $events = new events();
    $events->setRequesterId($user_id);

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
