<?php
require_once '../classes/class.constant.php';
require_once '../classes/class.events.php';

header('Content-Type: application/json');

$events = new events();
$result = $events->getAllEventsWithTicketsLatestFirst();

echo json_encode($result);
exit;
