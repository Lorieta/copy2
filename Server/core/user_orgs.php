<?php
class Organization {

    // Database connection and table name
    private $conn;
    private $table = 'user_organizations';

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
}
?>
