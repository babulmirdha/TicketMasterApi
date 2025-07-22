<?php
require_once '../classes/class.events.php';
require_once '../classes/class.constant.php';
header('Content-Type: application/json');

// Get inputs from POST (FormData or JSON)
$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$ticket_ids = isset($_POST['ticket_ids']) ? $_POST['ticket_ids'] : [];
$recipient_email = isset($_POST['recipient_email']) ? trim($_POST['recipient_email']) : '';

$ticket_ids = array_filter(array_map('intval', (array) $ticket_ids));

if ($user_id <= 0 || empty($ticket_ids) || empty($recipient_email)) {
    echo json_encode([
        "error" => true,
        "msg" => "Missing user_id, ticket IDs, or recipient email."
    ]);
    exit;
}

if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "error" => true,
        "msg" => "Invalid email format."
    ]);
    exit;
}

// Create event instance and set requesterId from frontend input
$event = new events();
$event->setRequesterId($user_id);

$response = $event->transferTickets($ticket_ids, $recipient_email);

echo json_encode($response);
exit;
