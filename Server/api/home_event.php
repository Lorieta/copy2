<?php
// Headers
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once(dirname(__FILE__) . '/../core/initialize.php');
    if (!$db) {
        throw new RuntimeException('Database connection failed');
    }

    // Create homeEvent instance
    $homeEvent = new homeEvent($db);

    // Retrieve events
    $events = $homeEvent->read();

    if ($events === null) {
        throw new RuntimeException('Event retrieval failed');
    }

    // Prepare response data
    $response = [
        'status' => 'success',
        'count' => count($events),
        'data' => $events
    ];

    // If no events were found, add a message
    if (empty($events)) {
        $response['message'] = 'No events found';
    }

    // Return the JSON response
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database operation failed'
    ]);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Unexpected Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ]);
}
?>
