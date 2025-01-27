<?php
class EventHandler {
    private $db;
    private $table = 'event_handler';

    public $event_id;
    public $event_title;
    public $event_des;
    public $date;
    public $date_started;
    public $date_ended;
    public $platform;
    public $platform_link;
    public $location;
    public $eventvisibility;
    public $banner;
    public $org_id;

    // Constructor receives the database connection
    public function __construct($db) {
        $this->db = $db;
    }

    // Method to create an event
    public function createEvent() {
        // Prepare SQL query to insert event data
        $query = "INSERT INTO " . $this->table . " 
                  (event_title, event_des, date, date_started, date_ended, platform, platform_link, location, eventvisibility, banner, org_id)
                  VALUES (:event_title, :event_des, :date, :date_started, :date_ended, :platform, :platform_link, :location, :eventvisibility, :banner, :org_id)";
        
        // Prepare the statement
        $stmt = $this->db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':event_title', $this->event_title);
        $stmt->bindParam(':event_des', $this->event_des);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':date_started', $this->date_started);
        $stmt->bindParam(':date_ended', $this->date_ended);
        $stmt->bindParam(':platform', $this->platform);
        $stmt->bindParam(':platform_link', $this->platform_link);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':eventvisibility', $this->eventvisibility);
        $stmt->bindParam(':banner', $this->banner);
        $stmt->bindParam(':org_id', $this->org_id);

        // Execute the query and check for success
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function read($org_id = null) {
        try {
            // Base query
            $query = 'SELECT 
            e.eventid,
            e.event_des,
            e.event_title,
            e.banner,
            e.date,
            e.date_started,
            e.date_ended,
            e.eventvisibility,
            e.platform,
            e.platform_link,
            e.location,
            o.org_id,
            o.org_name,
            o.org_desc
        FROM ' . $this->table . ' e
        JOIN organizations o ON e.org_id = o.org_id';

    
            // Add organization filter if provided
            if ($org_id !== null) {
                $query .= ' WHERE org_id = :org_id';
            }
    
            $stmt = $this->db->prepare($query);
            
            // Bind organization ID if provided
            if ($org_id !== null) {
                $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            }
    
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Process banner images
            foreach ($results as &$row) {
                if (!empty($row['banner'])) {
                    $row['banner'] = base64_encode($row['banner']);
                }
                // Ensure org_id is properly typed
                $row['org_id'] = $row['org_id'] !== null ? (int)$row['org_id'] : null;
            }
    
            return $results;
    
        } catch (PDOException $e) {
            error_log('Database error in EventHandler::read(): ' . $e->getMessage());
            return null;
        }
    }

    public function updateEvent() {
        // Prepare SQL query to update event data
        $query = "UPDATE " . $this->table . " SET 
                    event_title = :event_title, 
                    event_des = :event_des, 
                    date = :date, 
                    date_started = :date_started, 
                    date_ended = :date_ended, 
                    platform = :platform, 
                    platform_link = :platform_link, 
                    location = :location, 
                    eventvisibility = :eventvisibility, 
                    banner = :banner 
                  WHERE eventid = :event_id";
    
        // Prepare the statement
        $stmt = $this->db->prepare($query);
    
        // Bind parameters
        $stmt->bindParam(':event_title', $this->event_title);
        $stmt->bindParam(':event_des', $this->event_des);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':date_started', $this->date_started);
        $stmt->bindParam(':date_ended', $this->date_ended);
        $stmt->bindParam(':platform', $this->platform);
        $stmt->bindParam(':platform_link', $this->platform_link);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':eventvisibility', $this->eventvisibility);
        $stmt->bindParam(':banner', $this->banner);
        $stmt->bindParam(':event_id', $this->event_id);
    
        // Execute the query and return status
        return $stmt->execute();
    }

    public function delete() {
        // Prepare SQL query to delete an event
        $query = "DELETE FROM " . $this->table . " WHERE eventid = :event_id";
    
        // Prepare the statement
        $stmt = $this->db->prepare($query);
    
        // Bind the event ID parameter
        $stmt->bindParam(':event_id', $this->event_id);
    
        // Execute the query and return status
        return $stmt->execute();
    }

    public function read_single() {
        // Change column name from eventid to id
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :event_id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':event_id', $this->event_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
