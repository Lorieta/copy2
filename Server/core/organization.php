<?php
class Organization {

    // Database connection and table name
    private $conn;
    private $table = 'organizations';

    private $table2 = 'user_organizations';

    // Organization properties
    public $org_id;
    public $org_name;
    public $org_logo;
    public $org_desc;




    public function __construct($db) {
        $this->conn = $db;
    }

    // Retrieve organizations from the database
    public function read() {
        $query = 'SELECT 
            org_id, org_name, org_logo, org_desc
        FROM ' . $this->table . ' 
        ORDER BY org_id ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create_follow($student_id) {
        $query = 'INSERT INTO ' . $this->table2 . '
            SET
                student_id = :student_id,
                org_id = :org_id,
                date_admitted = :date_admitted';

        $stmt = $this->conn->prepare($query);

        // Clean and validate data
        $student_id = htmlspecialchars(strip_tags($student_id));
        $this->org_id = htmlspecialchars(strip_tags($this->org_id));
        $current_date = date('Y-m-d'); // Get current date

        // Bind parameters
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':org_id', $this->org_id);
        $stmt->bindParam(':date_admitted', $current_date);

        // Execute query
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Handle SQL errors (e.g., duplicate entry)
            if ($e->getCode() == '23000') { // Duplicate entry error code
                return false;
            }
            error_log("Error: " . $e->getMessage());
        }

        return false;
    }


}
?>
