<?php
class EmailHandler{
    
    private $conn;
    private $table = 'newsletter';

 
    public $foreign_email; 
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function create()
{

    $query = 'INSERT INTO ' . $this->table . ' 
              (foreign_email) 
              VALUES (:foreign_email)';
    
    $stmt = $this->conn->prepare($query);
    
    if (!$stmt) {
        echo "Error preparing statement: " . $this->conn->errorInfo()[2];
        return false;
    }
    $this->foreign_email = htmlspecialchars(strip_tags($this->foreign_email ?? ''));
       
    $stmt->bindParam(':foreign_email', $this->foreign_email);
    
    if ($stmt->execute()) {
        return true;
    }
    $errorInfo = $stmt->errorInfo();
    echo "Error: " . $errorInfo[2];
    return false;
}



}
?>