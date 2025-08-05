<?php 
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user_id and event_id from POST
    $user_id  = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    // Validate input
    if ($user_id <= 0 || $event_id <= 0) {
        echo json_encode([
            "error" => true,
            "error_code" => 400,
            "message" => "Invalid user_id or event_id."
        ]);
        exit;
    }

    // Initialize the events class
    $events = new events(); // assumes DB connection is handled in constructor

    // Call the toggleFavorite function to either add or remove the event from favorites
    $result = $events->toggleFavoriteEvent($user_id, $event_id);

    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
