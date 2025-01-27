<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Add error reporting control
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once dirname(__FILE__) . '/../core/initialize.php';

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID required', 400);
    }

    $event = new EventHandler($db);
    $event->event_id = $_GET['event_id'];
    
    if (!$result = $event->read_single()) {
        throw new Exception('Event not found', 404);
    }

    // Ensure consistent ID field name
    if (isset($result['id'])) {
        $result['event_id'] = $result['id'];
    }
    
    $result['banner'] = base64_encode($result['banner']);
    echo json_encode(['data' => $result]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
}
?>