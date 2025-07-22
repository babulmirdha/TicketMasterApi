<?php

require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';
require_once '../classes/class.imglib.php';


if (!empty($_POST)) {
    // Required fields
    $requiredFields = [
        'userId', 'artistName', 'eventName', 'section', 'row', 'seat',
        'date', 'location', 'time', 'ticketType', 'level', 'total_tickets'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
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

    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $currentTime = time();
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $tempPath = TEMP_PATH . "{$currentTime}." . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $tempPath)) {

            // echo json_encode("move_uploaded_file");

            $imgLib = new imglib();
            $response = $imgLib->createEventImage($tempPath, $tempPath);

        
            if (isset($response['imgUrl'])) {
                $image = $response['imgUrl'];
            }

            //  echo json_encode($response);

            unset($imgLib);
        }
    }


    // Sanitize and assign variables
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

    // Create new event
    $event = new events();
    $event->setRequesterId($userId);

    $result = $event->newEvent(
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
?>
