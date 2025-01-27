<?php
class homeEvent {
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

    public function read() {
        try {
            // Query with a JOIN to include org_name from organizations table
            $query = 'SELECT 
                e.eventid,
                e.event_des,
                e.event_title,
                e.banner,
                e.date,
                e.date_started,
                e.date_ended,
                e.location,
                e.org_id,
                o.org_name
            FROM ' . $this->table . ' e
            INNER JOIN organizations o ON e.org_id = o.org_id';
            
            // Prepare the SQL statement
            $stmt = $this->db->prepare($query);
    
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Process banner images
            foreach ($results as &$row) {
                if (!empty($row['banner'])) {
                    $row['banner'] = base64_encode($row['banner']);
                }
            }
    
            return $results;
        } catch (PDOException $e) {
            error_log('Database error in EventHandler::read(): ' . $e->getMessage());
            return null;
        }
    }
    
}
?>
