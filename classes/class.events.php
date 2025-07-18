<?php


require_once 'class.db_connect.php';
require_once 'class.constant.php';
require_once 'class.constant.php';


class events extends db_connect
{
    private $requesterId;

    private $opponentId;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function setRequesterId($requesterId)
    {
        $this->requesterId = $requesterId;
    }

    private function getRequesterId()
    {
        return $this->requesterId;
    }

    /**
     * @return mixed
     */
    public function getOpponentId()
    {
        return $this->opponentId;
    }

    /**
     * @param mixed $opponentId
     */
    public function setOpponentId($opponentId)
    {
        $this->opponentId = $opponentId;
    }

    public function newEvent(
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
    ) {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);
        $userId = $this->getRequesterId();
        $create_at = time();

        $sql = "
        INSERT INTO tbl_events (
            user_id, artist_name, event_name, section, row, seat,
            date, location, time, ticket_type, level, total_tickets, image, create_at
        ) VALUES (
            :user_id, :artist_name, :event_name, :section, :row, :seat,
            :date, :location, :time, :ticket_type, :level, :total_tickets, :image, :create_at
        )
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':artist_name', $artist_name, PDO::PARAM_STR);
        $stmt->bindParam(':event_name', $event_name, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_INT);
        $stmt->bindParam(':row', $row, PDO::PARAM_INT);
        $stmt->bindParam(':seat', $seat, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':ticket_type', $ticket_type, PDO::PARAM_STR);
        $stmt->bindParam(':level', $level, PDO::PARAM_STR);
        $stmt->bindParam(':total_tickets', $total_tickets, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':create_at', $create_at, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $eventId = $this->db->lastInsertId();

            // Insert into tbl_tickets
            $ticketSql = "
            INSERT INTO tbl_tickets (event_id, seat, create_at)
            VALUES (:event_id, :seat, :create_at) ";

            $ticketStmt = $this->db->prepare($ticketSql);

            for ($i = 0; $i < $total_tickets; $i++) {
                $currentSeat = $seat + $i;
                $ticketStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
                $ticketStmt->bindParam(':seat', $currentSeat, PDO::PARAM_INT);
                $ticketStmt->bindParam(':create_at', $create_at, PDO::PARAM_INT);
                $ticketStmt->execute();
            }

            $result['error'] = false;
            $result['error_code'] = ERROR_SUCCESS;
            $result['msg'] = "Event and tickets created successfully.";

        } else {
            $result['msg'] = "Failed to create event.";
        }

        return $result;
    }


    public function getUserEventsWithTickets() {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);

        $userId = $this->getRequesterId();

        // Fetch all events created by the user
        $stmt = $this->db->prepare("
        SELECT 
            e.*,
            t.id as ticket_id,
            t.seat as ticket_seat,
            t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        WHERE e.user_id = :user_id
        ORDER BY e.id DESC, t.id ASC
    ");

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $events = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = $row['id'];

                // Initialize event if not already
                if (!isset($events[$eventId])) {
                    $events[$eventId] = [
                        "event_id" => $eventId,
                        "artist_name" => $row['artist_name'],
                        "event_name" => $row['event_name'],
                        "section" => $row['section'],
                        "row" => $row['row'],
                        "seat" => $row['seat'],
                        "date" => $row['date'],
                        "location" => $row['location'],
                        "time" => $row['time'],
                        "ticket_type" => $row['ticket_type'],
                        "level" => $row['level'],
                        "total_tickets" => $row['total_tickets'],
                        "image" => $row['image'],
                        "create_at" => $row['create_at'],
                        "tickets" => []
                    ];
                }

                // Add ticket if exists
                if (!empty($row['ticket_id'])) {
                    $events[$eventId]['tickets'][] = [
                        "ticket_id" => $row['ticket_id'],
                        "seat" => $row['ticket_seat'],
                        "create_at" => $row['ticket_created_at']
                    ];
                }
            }

            $result['error'] = false;
            $result['error_code'] = ERROR_SUCCESS;
            $result['data'] = array_values($events); // reset keys for JSON response
        }

        return $result;
    }

    public function searchEvents($searchText) {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);
        $userId = $this->getRequesterId();

        $likeSearch = "%" . $searchText . "%";

        $stmt = $this->db->prepare("
        SELECT e.*, t.id as ticket_id, t.seat as ticket_seat, t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        WHERE (
            e.event_name LIKE :search
            OR e.artist_name LIKE :search
            OR e.location LIKE :search
        )
        ORDER BY e.id DESC, t.id ASC
        LIMIT 100
    ");

        $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $events = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = $row['id'];

                if (!isset($events[$eventId])) {
                    $events[$eventId] = [
                        "event_id" => $eventId,
                        "user_id" => $row['user_id'],
                        "artist_name" => $row['artist_name'],
                        "event_name" => $row['event_name'],
                        "section" => $row['section'],
                        "row" => $row['row'],
                        "seat" => $row['seat'],
                        "date" => $row['date'],
                        "location" => $row['location'],
                        "time" => $row['time'],
                        "ticket_type" => $row['ticket_type'],
                        "level" => $row['level'],
                        "total_tickets" => $row['total_tickets'],
                        "image" => $row['image'],
                        "create_at" => $row['create_at'],
                        "tickets" => []
                    ];
                }

                if (!empty($row['ticket_id'])) {
                    $events[$eventId]['tickets'][] = [
                        "ticket_id" => $row['ticket_id'],
                        "seat" => $row['ticket_seat'],
                        "create_at" => $row['ticket_created_at']
                    ];
                }
            }

            $result['error'] = false;
            $result['error_code'] = ERROR_SUCCESS;
            $result['data'] = array_values($events);
        }

        return $result;
    }


}