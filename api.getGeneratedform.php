<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'classes/class.accounts.php'; // Your DB connection class
require_once 'classes/class.db_connect.php';

class TicketAPI extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    // Fetch all tickets
    public function getTickets()
    {
        $result = ["error" => true, "msg" => "No tickets found", "tickets" => []];

        $stmt = $this->db->prepare("SELECT id, artist_name, event_name, section, row_number, seat, event_date, location, event_time, ticket_type, level, ticket_quantity, image_path, created_at FROM tbl_ticketform ORDER BY created_at DESC");
        
        if ($stmt->execute()) {
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($tickets) {
                $result = ["error" => false, "msg" => "Tickets fetched successfully", "tickets" => $tickets];
            }
        } else {
            $result = ["error" => true, "msg" => "Failed to fetch tickets", "tickets" => []];
        }

        return $result;
    }
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new TicketAPI();
    $response = $api->getTickets();
    echo json_encode($response);
} else {
    echo json_encode(['error' => true, 'msg' => 'Invalid request method']);
}
