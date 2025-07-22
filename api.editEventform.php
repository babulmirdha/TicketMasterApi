<?php
require_once 'classes/class.constant.php';
require_once 'classes/class.imglib.php';
require_once 'classes/class.events.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'error' => true,
        'msg' => 'Event ID is required.'
    ]);
    exit;
}

$id = (int) $_GET['id'];

if (!empty($_POST)) {
    $requiredFields = [
        'userId', 'artistName', 'eventName', 'section', 'row', 'seat',
        'date', 'location', 'time', 'ticketType', 'level', 'total_tickets'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        echo json_encode([
            'error' => true,
            'msg' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        exit;
    }

    $userId = (int) $_POST['userId'];
    $artistName = trim($_POST['artistName']);
    $eventName = trim($_POST['eventName']);
    $section = (int) $_POST['section'];
    $row = (int) $_POST['row'];
    $seat = (int) $_POST['seat'];
    $date = trim($_POST['date']);
    $location = trim($_POST['location']);
    $time = trim($_POST['time']);
    $ticketType = trim($_POST['ticketType']);
    $level = trim($_POST['level']);
    $totalTickets = (int) $_POST['total_tickets'];

    $event = new events();
    $event->setRequesterId($userId);

    // ✅ Get old image path first
    $oldEvent = $event->getEventById($id); // You must implement getEventById($id) in class.events.php
    $oldImagePath = $oldEvent && isset($oldEvent['image']) ? $oldEvent['image'] : null;

    $image = $oldImagePath;

    // ✅ Handle image upload + replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $currentTime = time();
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $tempPath = TEMP_PATH . "{$currentTime}." . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $tempPath)) {
            $imgLib = new imglib();
            $response = $imgLib->createEventImage($tempPath, $tempPath);

            if (isset($response['imgUrl']) && !$response['error']) {
                $image = $response['imgUrl'];

                // ✅ Delete old image file if it's not null and different
                if ($oldImagePath && $oldImagePath !== $image) {
                    $absolutePath = __DIR__ . $oldImagePath;
                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }
                }
            }

            unset($imgLib);
        }
    }

    $result = $event->updateEvent(
        $id,
        $artistName,
        $eventName,
        $section,
        $row,
        $seat,
        $date,
        $location,
        $time,
        $ticketType,
        $level,
        $totalTickets,
        $image
    );

    echo json_encode($result);
    exit;
}
