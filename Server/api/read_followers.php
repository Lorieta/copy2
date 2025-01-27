<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once(dirname(__FILE__) . '/../core/initialize.php');

// Updated session validation
if (!isset($_SESSION['logged_in']) || 
    (empty($_SESSION['org_ids']) && empty($_SESSION['org_id']))) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$userHandler = new UserHandler($db);
$stmt = $userHandler->read();

if ($stmt) {
    $followers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($followers) > 0) {
        $response = [
            'status' => 'success',
            'data' => array_map(function($follower) {
                return [
                    'student_id' => $follower['student_id'],
                    'firstname' => htmlspecialchars($follower['firstname']),
                    'lastname' => htmlspecialchars($follower['lastname']),
                    'email' => htmlspecialchars($follower['email']),
                    'account_type' => $follower['account_type'],
                    'org_id' => $follower['org_id']
                ];
            }, $followers)
        ];
        
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'empty', 'message' => 'No followers found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>