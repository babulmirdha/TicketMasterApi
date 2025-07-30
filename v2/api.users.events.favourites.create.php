<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if (!empty($_POST)) {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['eventId']) ? intval($_POST['eventId']) : 0;

    if ($user_id > 0 && $eventId > 0) {
        $events = new events();  // or favourites class if separate
        $result = $events->addFavouriteEvent($user_id, $eventId);

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
