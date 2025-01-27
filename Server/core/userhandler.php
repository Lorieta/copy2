<?php
class UserHandler {

    // Database connection and table name
    private $conn;
    private $table = 'users';

    // User properties
    public $student_id;
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $account_type;


    public function __construct($db) {
        $this->conn = $db;
    }
  
    public function read() {
        $query = 'SELECT 
            u.student_id,
            u.firstname,
            u.lastname,
            u.email,
            u.account_type,
            uo.org_id
        FROM ' . $this->table . ' u
        LEFT JOIN user_organizations uo ON u.student_id = uo.student_id
        ORDER BY u.student_id ASC';
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    
        return $stmt;
    }
    //retrieve the number of users from the database
 

    public function read_single() {
        $query = 'SELECT 
            student_id,
            firstname,
            lastname,
            email,
            account_type
        FROM ' . $this->table . ' 
        WHERE student_id = ? LIMIT 1';
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->student_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $this->firstname = $row['firstname'];
        $this->lastname = $row['lastname'];
        $this->email = $row['email'];
        $this->account_type = $row['account_type'];
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (student_id, firstname, lastname, email, password, account_type) 
                  VALUES (?, ?, ?, ?, ?, ?)";
                  
        $stmt = $this->conn->prepare($query);
        error_log("Raw password before hashing: " . $this->password);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        error_log("Hashed password: " . $this->password);
        return $stmt->execute([
            $this->student_id,
            $this->firstname,
            $this->lastname,
            $this->email,
            $this->password,
            $this->account_type
        ]);
        
    }
    
    public function login($email, $password) {
        $query = "SELECT 
            s.student_id, 
            s.firstname, 
            s.lastname, 
            s.email,
            s.password,
            s.account_type, 
            so.org_id
        FROM 
            users s 
            LEFT JOIN user_organizations so 
            ON s.student_id = so.student_id
        WHERE s.email = :email";
    
        // Debug: Log the query
        error_log("Executing query: $query");
    
        $stmt = $this->conn->prepare($query);
    
        // Bind parameters
        $stmt->bindParam(':email', $email);
    
        // Execute the query
        $stmt->execute();
    

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Debug: Log the raw database results
        error_log("Raw database results: " . json_encode($rows));
    
        if (count($rows) > 0) {
            $userData = $rows[0];
            $hashed_password = $userData['password'];
            unset($userData['password']);
    
            // Collect org_ids
            $orgIds = array();
            foreach ($rows as $row) {
                if (!is_null($row['org_id'])) {
                    $orgIds[] = $row['org_id'];
                }
            }
    
            // Debug: Log the org_ids
            error_log("Aggregated org_ids: " . json_encode($orgIds));

       
    
            // Debug: Log fetched data (excluding sensitive info like passwords)
            error_log("Fetched user data: " . json_encode([
                'student_id' => $row['student_id'],
                'account_type' => $row['account_type'],
                'org_id' => $row['org_id']
            ]));
    
            $hashed_password = $row['password'];
    
            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // If the user is an admin, we only return one org_id, not an array
                if ($userData['account_type'] === 'Admin') {
                    $userData['org_id'] = $orgIds[0]; // Just the first org_id for admin
                    unset($userData['org_ids']); // Remove org_ids array for admins
                } else {
                    $userData['org_ids'] = $orgIds; // Regular users get all org_ids
                }
                return $userData;
            } else {
                return false; // Return false if password doesn't match
            }
        } else {
            return false; // Return false if no user found
               
        }
    
        return false; // Return false on failure
    }
}