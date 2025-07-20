<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'classes/class.accounts.php'; // your DB connection class
require_once 'classes/class.db_connect.php';

class TicketAPI extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function saveTicket($data, $file)
    {
        $result = ["error" => true, "msg" => "Unknown error"];

        // Basic validations
        $requiredFields = ['artist_name', 'event_name', 'event_date', 'ticket_quantity'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ["error" => true, "msg" => "Field '$field' is required"];
            }
        }

        // Validate number
        if (!is_numeric($data['ticket_quantity']) || $data['ticket_quantity'] <= 0) {
            return ["error" => true, "msg" => "Invalid number of tickets"];
        }

        // Validate date format (YYYY-MM-DD)
        $d = DateTime::createFromFormat('Y-m-d', $data['event_date']);
        if (!$d || $d->format('Y-m-d') !== $data['event_date']) {
            return ["error" => true, "msg" => "Invalid date format, expected YYYY-MM-DD"];
        }

        // Handle image upload if exists
        $image_path = null;
        if ($file && isset($file['image']) && $file['image']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = __DIR__ . '/uploads/tickets/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $tmp_name = $file['image']['tmp_name'];
            $ext = pathinfo($file['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('ticket_') . '.' . $ext;

            $destPath = $uploadDir . $filename;

            if (move_uploaded_file($tmp_name, $destPath)) {
                $image_path = 'uploads/tickets/' . $filename;
            } else {
                return ["error" => true, "msg" => "Failed to upload image"];
            }
        }

        // Prepare SQL insert
        // Prepare SQL insert
        $stmt = $this->db->prepare("INSERT INTO tbl_ticketform 
    (artist_name, event_name, section, row_number, seat, event_date, location, event_time, ticket_type, level, ticket_quantity, image_path) 
    VALUES 
    (:artist_name, :event_name, :section, :row_number, :seat, :event_date, :location, :event_time, :ticket_type, :level, :ticket_quantity, :image_path)");

        $stmt->bindValue(':artist_name', $data['artist_name'], PDO::PARAM_STR);
        $stmt->bindValue(':event_name', $data['event_name'], PDO::PARAM_STR);
        $stmt->bindValue(':section', $data['section'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':row_number', $data['row_number'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':seat', $data['seat'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $data['event_date'], PDO::PARAM_STR);
        $stmt->bindValue(':location', $data['location'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':event_time', $data['event_time'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':ticket_type', $data['ticket_type'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':level', $data['level'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':ticket_quantity', $data['ticket_quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':image_path', $image_path, PDO::PARAM_STR);


        if ($stmt->execute()) {
            $result = ["error" => false, "msg" => "Ticket saved successfully", "ticket_id" => $this->db->lastInsertId()];
        } else {
            $result = ["error" => true, "msg" => "Failed to save ticket"];
        }

        return $result;
    }
}

// Handle the POST request

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $api = new TicketAPI();

    // Collect POST fields (assuming form-data)
    $postData = [
        'artist_name' => $_POST['artist_name'] ?? null,
        'event_name' => $_POST['event_name'] ?? null,
        'section' => $_POST['section'] ?? null,
        'row_number' => $_POST['row_number'] ?? null,
        'seat' => $_POST['seat'] ?? null,
        'event_date' => $_POST['event_date'] ?? null,
        'location' => $_POST['location'] ?? null,
        'event_time' => $_POST['event_time'] ?? null,
        'ticket_type' => $_POST['ticket_type'] ?? null,
        'level' => $_POST['level'] ?? null,
        'ticket_quantity' => $_POST['ticket_quantity'] ?? null,
    ];

    $response = $api->saveTicket($postData, $_FILES);

    echo json_encode($response);
} else {
    echo json_encode(['error' => true, 'msg' => 'Invalid request method']);
}
