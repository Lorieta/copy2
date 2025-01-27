<?php
class Calendar {

    // Database connection and table name
    private $conn;
    private $table = 'calendar_events';

    // Organization properties
    public $calendar_id;
    public $student_id;
    public $event_id;
    public $org_id;
    public $is_attending;
    public $added_at;
    public $visibility_status;
    


    public function __construct($db) {
        $this->conn = $db;
    }

    // Retrieve organizations from the database
    public function read() {
        $query = 'SELECT 
            c.calendar_id, 
            c.student_id, 
            c.event_id, 
            c.org_id, 
            c.is_attending, 
            c.added_at, 
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
            o.org_name,
            o.org_desc
        FROM ' . $this->table . ' c
        INNER JOIN event_handler e ON c.event_id = e.eventid
        INNER JOIN organizations o on c.org_id = o.org_id
        ORDER BY c.calendar_id ASC';

        

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

   
    
    public function exists($student_id, $event_id, $org_id) {
        $query = 'SELECT COUNT(*) FROM ' . $this->table . ' 
                  WHERE student_id = :student_id 
                  AND event_id = :event_id 
                  AND org_id = :org_id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':student_id' => $student_id,
            ':event_id' => $event_id,
            ':org_id' => $org_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function add($data) {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (student_id, event_id, org_id, is_attending, added_at, visibility_status) 
                  VALUES 
                  (:student_id, :event_id, :org_id, :is_attending, :added_at, :visibility_status)';

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':event_id' => $data['event_id'],
            ':org_id' => $data['org_id'],
            ':is_attending' => $data['is_attending'],
            ':added_at' => $data['added_at'],
            ':visibility_status' => $data['visibility_status']
        ]);
    }
}
   
?>
