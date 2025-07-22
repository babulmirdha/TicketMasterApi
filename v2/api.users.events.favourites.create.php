<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if (!empty($_POST)) {
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $eventId = isset($_POST['eventId']) ? intval($_POST['eventId']) : 0;

    if ($userId > 0 && $eventId > 0) {
        $events = new events();  // or favourites class if separate
        $result = $events->addFavouriteEvent($userId, $eventId);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    } else {
        echo json_encode([
            "error" => true,
            "error_code" => 1,
            "message" => "Invalid user or event ID."
        ]);
        exit;
    }
}
