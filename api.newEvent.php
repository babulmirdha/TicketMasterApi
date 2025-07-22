<?php

require_once 'classes/class.constant.php';
require_once 'classes/class.events.php';
require_once 'classes/class.imglib.php';


if (!empty($_POST)) {
    // Required fields
    $requiredFields = [
        'user_id', 'artist_name', 'event_name', 'section', 'row', 'seat',
        'date', 'location', 'time', 'ticket_type', 'level', 'total_tickets'
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
    $user_id = (int) $_POST['user_id'];
    $artist_name = trim($_POST['artist_name']);
    $event_name = trim($_POST['event_name']);
    $section = (int) $_POST['section'];
    $row = (int) $_POST['row'];
    $seat = (int) $_POST['seat'];
    $date = trim($_POST['date']);
    $location = trim($_POST['location']);
    $time = trim($_POST['time']);
    $ticket_type = trim($_POST['ticket_type']);
    $level = trim($_POST['level']);
    $total_tickets = (int) $_POST['total_tickets'];

    // Create new event
    $event = new events();
    $event->setRequesterId($user_id);

    $result = $event->newEvent(
        $artist_name,
        $event_name,
        $section,
        $row,
        $seat,
        $date,
        $location,
        $time,
        $ticket_type,
        $level,
        $total_tickets,
        $image
    );

    echo json_encode($result);
    exit;
}
?>
