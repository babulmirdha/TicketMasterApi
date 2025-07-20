<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'classes/class.accounts.php';
require_once 'classes/class.db_connect.php';

class TicketAPI extends db_connect
{
    public function __construct($dbo = null)
    {
        parent::__construct($dbo);
    }

    public function getTickets()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ticket_id,
                    artist_name,
                    event_name,
                    section,
                    row_number,
                    seat,
                    event_date,
                    location,
                    event_time,
                    ticket_type,
                    level,
                    ticket_quantity,
                    image_path,
                    created_at
                FROM tbl_ticketform
                ORDER BY created_at DESC
            ");

            if (!$stmt->execute()) {
                throw new Exception("DB execution failed");
            }

            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($tickets)) {
                return [
                    "error"   => true,
                    "msg"     => "No tickets found",
                    "tickets" => []
                ];
            }

            return [
                "error"   => false,
                "msg"     => "Tickets fetched successfully",
                "tickets" => $tickets
            ];

        } catch (Exception $e) {
            return [
                "error"   => true,
                "msg"     => "Failed to fetch tickets: " . $e->getMessage(),
                "tickets" => []
            ];
        }
    }
}

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $api = new TicketAPI();
    echo json_encode($api->getTickets());
} else {
    echo json_encode([
        "error" => true,
        "msg"   => "Invalid request method",
        "tickets" => []
    ]);
}
