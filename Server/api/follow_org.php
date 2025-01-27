<?php
// follow_organization.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

require_once(dirname(__FILE__) . '/../core/initialize.php'); // Assuming this initializes the database connection and session

// Enable debug mode (set to true for debugging, false for production)
$debug = true;

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response = ['message' => 'Method not allowed'];
    if ($debug) {
        $response['debug'] = ['method_received' => $_SERVER['REQUEST_METHOD']];
    }
    echo json_encode($response);
    exit;
}


if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    $response = ['message' => 'Not logged in'];
    if ($debug) {
        $response['debug'] = ['session' => $_SESSION];
    }
    echo json_encode($response);
    exit;
}

// Get and validate input
$data = json_decode(file_get_contents("php://input"), true); // true to return as associative array
if ($debug) {
    error_log('Raw input: ' . file_get_contents("php://input")); // Log raw input for debugging
    error_log('Decoded input: ' . print_r($data, true)); // Log decoded data
}

if (!isset($data['org_id']) || !is_numeric($data['org_id'])) {
    http_response_code(400);
    $response = ['message' => 'Invalid organization ID'];
    if ($debug) {
        $response['debug'] = ['input' => $data];
    }
    echo json_encode($response);
    exit;
}

// Initialize Organization object
$organization = new Organization($db); // Assuming $db is initialized in core/initialize.php
$organization->org_id = (int)$data['org_id'];

// Attempt to create follow relationship
if ($organization->create_follow($_SESSION['student_id'])) {
    http_response_code(201);
    echo json_encode(['message' => 'Successfully followed organization']);
    exit;
}

// Check if the follow already exists
$query = 'SELECT * FROM user_organizations 
          WHERE student_id = :student_id AND org_id = :org_id';
$stmt = $db->prepare($query);
$stmt->execute([
    ':student_id' => $_SESSION['student_id'],
    ':org_id' => $data['org_id']
]);

if ($stmt->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(['message' => 'Already following this organization']);
} else {
    $errorInfo = $stmt->errorInfo(); // Get error details from PDO
    http_response_code(500);
    $response = ['message' => 'Failed to follow organization'];
    if ($debug) {
        $response['debug'] = ['query_error' => $errorInfo];
    }
    echo json_encode($response);
}
?>
