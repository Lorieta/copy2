<?php
// Allow CORS and specify the allowed methods
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true'); // Allow credentials

// Include initialization script
require_once(dirname(__FILE__) . '/../core/initialize.php');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

// Check if the user is logged in (org_ids is set in the session)
if (!isset($_SESSION['org_ids']) || !isset($_SESSION['student_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Unauthorized access. Please log in.']);
    exit;
}

// Fetch user data to check account type
$userHandler = new UserHandler($db);
$userHandler->student_id = $_SESSION['student_id']; // Assuming student_id is stored in the session
$userHandler->read_single();

// Check if user is an admin
if ($_SESSION['account_type'] !== 'Admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['message' => 'You are not authorized to create events.']);
    exit;
}

// Validate and process the form data
if (
    isset($_POST['event_title']) && 
    isset($_POST['event_des']) && 
    isset($_POST['date']) &&
    isset($_POST['date_started']) &&
    isset($_POST['platform']) && 
    isset($_POST['date_ended']) && 
    isset($_POST['platform_link']) &&
    isset($_POST['location']) && 
    isset($_POST['eventvisibility']) && 
    isset($_FILES['banner'])
) {
    $event = new EventHandler($db);

    // Assign organization ID from the session
    $event->org_id = $_SESSION['org_ids'][0]; // Get the first org_id if it's an array

    // Assign data to the event object
    $event->event_title = $_POST['event_title'];
    $event->event_des = $_POST['event_des'];
    $event->date = $_POST['date'];
    $event->date_started = $_POST['date_started'];
    $event->date_ended = $_POST['date_ended'];
    $event->platform = $_POST['platform'];
    $event->platform_link = $_POST['platform_link'];
    $event->location = $_POST['location'];
    $event->eventvisibility = $_POST['eventvisibility'];

    // Handle the banner upload
    if ($_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['banner']['type'], $allowed_types)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Invalid file type.']);
            exit;
        }

        if ($_FILES['banner']['size'] > 5 * 1024 * 1024) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'File is too large.']);
            exit;
        }

        $event->banner = file_get_contents($_FILES['banner']['tmp_name']);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'File upload error.']);
        exit;
    }

    // Create the event
    if ($event->createEvent()) {
        http_response_code(201); // Created
        echo json_encode(['message' => 'Event created successfully.']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['message' => 'Failed to create the event.']);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Missing required fields.']);
}
?>
