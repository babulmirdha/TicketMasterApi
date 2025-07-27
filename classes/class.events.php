<?php


require_once 'class.db_connect.php';
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
    public function getAllEventsWithTicketsLatestFirst()
    {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("
        SELECT 
            e.*,
            t.id as ticket_id,
            t.seat as ticket_seat,
            t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        ORDER BY e.create_at DESC, e.id DESC, t.id ASC
    ");

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
        $user_id = $this->getRequesterId();
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

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
            INSERT INTO tbl_tickets (event_id, seat, create_at, userId)
            VALUES (:event_id, :seat, :create_at, :user_id) ";

            $ticketStmt = $this->db->prepare($ticketSql);

            for ($i = 0; $i < $total_tickets; $i++) {
                $currentSeat = $seat + $i;
                $ticketStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
                $ticketStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
    public function updateEvent(
        $eventId,
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
        $image = null
    ) {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);

        $userId = $this->getRequesterId();

        // First, verify that this event belongs to the requester to prevent unauthorized updates
        $checkSql = "SELECT user_id FROM tbl_events WHERE id = :event_id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $checkStmt->execute();
        $ownerId = $checkStmt->fetchColumn();

        // if (!$ownerId || $ownerId != $userId) {
        //     $result['msg'] = "Unauthorized or event not found.";
        //     return $result;
        // }
        if (!$ownerId) {
            $result['msg'] = "Event not found.";
            return $result;
        }

        if ($ownerId != $userId) {
            $result['msg'] = "Unauthorized: You do not own this event.";
            return $result;
        }

        // Build SQL dynamically to update image only if provided
        $sql = "UPDATE tbl_events SET
                artist_name = :artist_name,
                event_name = :event_name,
                section = :section,
                row = :row,
                seat = :seat,
                date = :date,
                location = :location,
                time = :time,
                ticket_type = :ticket_type,
                level = :level,
                total_tickets = :total_tickets";

        if ($image !== null && $image !== '') {
            $sql .= ", image = :image";
        }

        $sql .= " WHERE id = :event_id";

        $stmt = $this->db->prepare($sql);

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

        if ($image !== null && $image !== '') {
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        }

        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Optionally, update tickets here if your logic requires it

            $result['error'] = false;
            $result['error_code'] = ERROR_SUCCESS;
            $result['msg'] = "Event updated successfully.";
        } else {
            $result['msg'] = "Failed to update event.";
        }

        return $result;
    }
    public function getEventById($eventId)
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_events WHERE id = :event_id");
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }



    public function getAllupcomingEventsWithTickets()
    {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);

        // Get today's date in Y-m-d format
        $today = date('Y-m-d');

        $stmt = $this->db->prepare("
        SELECT 
            e.*,
            t.id as ticket_id,
            t.seat as ticket_seat,
            t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        WHERE e.date >= :today
        ORDER BY e.date ASC, e.id ASC, t.id ASC
    ");

        $stmt->bindParam(':today', $today);

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
            $result['data'] = array_values($events); // reindex array
        }

        return $result;
    }

    public function getUserEventsWithTickets()
    {
        $result = array("error" => true, "error_code" => ERROR_UNKNOWN);

        $user_id = $this->getRequesterId();
        $today = date('Y-m-d'); // Current date in Y-m-d format

        $stmt = $this->db->prepare("
        SELECT 
            e.*,
            t.id as ticket_id,
            t.seat as ticket_seat,
            t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        WHERE e.user_id = :user_id AND e.date >= :today
        ORDER BY e.date ASC, e.id ASC, t.id ASC
    ");

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':today', $today, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $events = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = $row['id'];

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
            $result['data'] = array_values($events); // Reindex array
        }

        return $result;
    }


    public function searchEvents($searchText)
    {
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



    public function getFavouriteEventsForUser()
    {
        $result = ["error" => true, "error_code" => ERROR_UNKNOWN, "data" => []];

        $userId = $this->getRequesterId();

        if (!$userId) {
            $result['error_code'] = 401; // Unauthorized or invalid user ID
            $result['error'] = true;
            return $result;
        }

        try {
            $stmt = $this->db->prepare("
            SELECT 
                e.*,
                t.id as ticket_id,
                t.seat as ticket_seat,
                t.create_at as ticket_created_at
            FROM tbl_favourite_events f
            JOIN tbl_events e ON f.event_id = e.id
            LEFT JOIN tbl_tickets t ON e.id = t.event_id
            WHERE f.user_id = :user_id
            ORDER BY f.created_at DESC, t.id ASC
        ");

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $events = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = $row['id'];

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
        } catch (PDOException $e) {
            $result['error_code'] = 500;
            $result['error'] = true;
            $result['message'] = "Database error: " . $e->getMessage();
        }

        return $result;
    }
    public function addFavouriteEvent($userId, $eventId)
    {
        $result = ["error" => true, "error_code" => ERROR_UNKNOWN, "message" => "Unknown error"];

        // Validate user ID exists
        $stmtUser = $this->db->prepare("SELECT id FROM tbl_users WHERE id = :user_id");
        $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtUser->execute();
        if (!$stmtUser->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "User not found.";
            return $result;
        }

        // Validate event ID exists
        $stmtEvent = $this->db->prepare("SELECT id FROM tbl_events WHERE id = :event_id");
        $stmtEvent->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtEvent->execute();
        if (!$stmtEvent->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "Event not found.";
            return $result;
        }

        // Check if already favourited to avoid duplicates
        $stmtCheck = $this->db->prepare("SELECT id FROM tbl_favourite_events WHERE user_id = :user_id AND event_id = :event_id");
        $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->fetch()) {
            // Already favourited
            $result['error'] = false;
            $result['error_code'] = 0;
            $result['message'] = "Event already in favourites.";
            return $result;
        }

        // Insert into favourites
        $stmtInsert = $this->db->prepare("INSERT INTO tbl_favourite_events (user_id, event_id, created_at) VALUES (:user_id, :event_id, NOW())");
        $stmtInsert->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtInsert->bindParam(':event_id', $eventId, PDO::PARAM_INT);

        if ($stmtInsert->execute()) {
            $result['error'] = false;
            $result['error_code'] = 0;
            $result['message'] = "Event added to favourites.";
        } else {
            $errorInfo = $stmtInsert->errorInfo();
            $result['message'] = "Failed to add favourite event: " . $errorInfo[2];
        }

        return $result;
    }
    public function removeFavouriteEvent($userId, $eventId)
    {
        $result = ["error" => true, "error_code" => ERROR_UNKNOWN, "message" => "Unknown error"];

        // Validate user ID exists
        $stmtUser = $this->db->prepare("SELECT id FROM tbl_users WHERE id = :user_id");
        $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtUser->execute();
        if (!$stmtUser->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "User not found.";
            return $result;
        }

        // Validate event ID exists
        $stmtEvent = $this->db->prepare("SELECT id FROM tbl_events WHERE id = :event_id");
        $stmtEvent->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtEvent->execute();
        if (!$stmtEvent->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "Event not found.";
            return $result;
        }

        // Check if favourite exists
        $stmtCheck = $this->db->prepare("SELECT id FROM tbl_favourite_events WHERE user_id = :user_id AND event_id = :event_id");
        $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtCheck->execute();

        if (!$stmtCheck->fetch()) {
            // Not in favourites
            $result['error_code'] = 404;
            $result['message'] = "Favourite event not found.";
            return $result;
        }

        // Delete favourite
        $stmtDelete = $this->db->prepare("DELETE FROM tbl_favourite_events WHERE user_id = :user_id AND event_id = :event_id");
        $stmtDelete->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtDelete->bindParam(':event_id', $eventId, PDO::PARAM_INT);

        if ($stmtDelete->execute()) {
            $result['error'] = false;
            $result['error_code'] = 0;
            $result['message'] = "Favourite event removed.";
        } else {
            $errorInfo = $stmtDelete->errorInfo();
            $result['message'] = "Failed to remove favourite event: " . $errorInfo[2];
        }

        return $result;
    }
    public function toggleFavoriteEvent($userId, $eventId)
    {
        $result = ["error" => true, "error_code" => ERROR_UNKNOWN, "message" => "Unknown error"];

        // Validate user ID exists
        $stmtUser = $this->db->prepare("SELECT id FROM tbl_users WHERE id = :user_id");
        $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtUser->execute();
        if (!$stmtUser->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "User not found.";
            return $result;
        }

        // Validate event ID exists
        $stmtEvent = $this->db->prepare("SELECT id FROM tbl_events WHERE id = :event_id");
        $stmtEvent->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtEvent->execute();
        if (!$stmtEvent->fetch()) {
            $result['error_code'] = 404;
            $result['message'] = "Event not found.";
            return $result;
        }

        // Check if the event is already favorited
        $stmtCheck = $this->db->prepare("SELECT id FROM tbl_favourite_events WHERE user_id = :user_id AND event_id = :event_id");
        $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmtCheck->execute();

        // If favorited, remove it, else add it
        if ($stmtCheck->fetch()) {
            // Remove the favorite
            $result = $this->removeFavouriteEvent($userId, $eventId);
        } else {
            // Add the favorite
            $result = $this->addFavouriteEvent($userId, $eventId);
        }

        return $result;
    }

    public function getPastEvents()
    {
        $result = ["error" => true, "error_code" => ERROR_UNKNOWN];

        // Get current date
        $today = date('Y-m-d');

        $sql = "
        SELECT 
            e.*,
            t.id as ticket_id,
            t.seat as ticket_seat,
            t.create_at as ticket_created_at
        FROM tbl_events e
        LEFT JOIN tbl_tickets t ON e.id = t.event_id
        WHERE e.date < :today
        ORDER BY e.date DESC, t.id ASC
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':today', $today, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $events = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = $row['id'];

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

    public function transferTickets(array $ticket_ids, string $recipient_email)
    {
        $senderId = $this->getRequesterId();
        $placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));

        $result = ["error" => true, "msg" => "Unknown error"];

        // 1. Check if recipient email exists
        $stmt = $this->db->prepare("SELECT id FROM tbl_users WHERE email = ?");
        $stmt->execute([$recipient_email]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            $result["msg"] = "Recipient email not found in users list.";
            return $result;
        }

        $recipientId = (int)$recipient['id'];

        // 2. Verify sender owns all tickets
        $checkStmt = $this->db->prepare("
        SELECT id FROM tbl_tickets 
        WHERE id IN ($placeholders) AND userId = ?
    ");
        $params = array_merge($ticket_ids, [$senderId]);
        $checkStmt->execute($params);
        $ownedTickets = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($ownedTickets) !== count($ticket_ids)) {
            $result["msg"] = "One or more tickets do not belong to you.";
            return $result;
        }

        // 3. Update tickets to recipient userId
        $updateStmt = $this->db->prepare("
        UPDATE tbl_tickets SET userId = ? WHERE id IN ($placeholders)
    ");
        $updateParams = array_merge([$recipientId], $ticket_ids);

        if ($updateStmt->execute($updateParams)) {
            $result["error"] = false;
            $result["msg"] = "Ticket(s) successfully transferred.";
        } else {
            $result["msg"] = "Failed to transfer tickets.";
        }

        return $result;
    }
}
