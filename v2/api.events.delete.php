<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';
require_once '../classes/class.imglib.php';

// Start session if needed (for accessing user_id)
session_start();

// Check if the user is logged in (you might adjust this depending on your app's auth method)
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => true,
        'msg' => 'Unauthorized access'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Validate event ID
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
    echo json_encode([
        'error' => true,
        'msg' => 'Valid event ID is required'
    ]);
    exit;
}

$id = (int) $_GET['id'];

// Handle POST request to delete event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Optional: Check if _delete flag is set to confirm deletion
    if (!isset($_POST['_delete']) || $_POST['_delete'] !== 'true') {
        echo json_encode([
            'error' => true,
            'msg' => 'Delete confirmation flag is missing or invalid'
        ]);
        exit;
    }

    $event = new events();
    $event->setRequesterId($user_id);

    $result = $event->deleteEvent($id);

    echo json_encode($result);
    exit;
}

// If not a POST request
echo json_encode([
    'error' => true,
    'msg' => 'Invalid request method'
]);
exit;
?>
