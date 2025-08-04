<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';
require_once '../classes/class.imglib.php';

header('Content-Type: application/json');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'error' => true,
        'msg' => 'Invalid request method. Use POST.'
    ]);
    exit;
}

// Validate POST parameters
if (
    !isset($_POST['user_id']) || !is_numeric($_POST['user_id']) || (int)$_POST['user_id'] <= 0 ||
    !isset($_POST['id']) || !is_numeric($_POST['id']) || (int)$_POST['id'] <= 0
) {
    echo json_encode([
        'error' => true,
        'msg' => 'Missing or invalid user_id or event id'
    ]);
    exit;
}

// Assign variables
$user_id = (int) $_POST['user_id'];
$event_id = (int) $_POST['id'];

// Delete the event
$event = new events();
$event->setRequesterId($user_id);
$result = $event->deleteEvent($event_id);

// Return JSON response
echo json_encode($result);
exit;
?>
