<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once(__DIR__ . '/../../core/initialize.php');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['org_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$event_id = $input['event_id'] ?? null;

if (!$event_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing event ID']);
    exit;
}

try {
    // Verify ownership
    $stmt = $db->prepare("SELECT org_id FROM event_handler WHERE eventid = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        http_response_code(404);
        echo json_encode(['message' => 'Event not found']);
        exit;
    }

    if ($event['org_id'] != $_SESSION['org_id']) {
        http_response_code(403);
        echo json_encode(['message' => 'Permission denied']);
        exit;
    }

    $eventHandler = new EventHandler($db);
    $eventHandler->event_id = $event_id;

    if ($eventHandler->delete()) {
        http_response_code(200);
        echo json_encode(['message' => 'Event deleted']);
    } else {
        throw new Exception('Failed to delete event');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
?>