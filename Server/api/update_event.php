<?php
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../core/initialize.php';

try {
    // Basic validation
    if (empty($_POST['event_id'])) {
        throw new Exception('Event ID required');
    }

    $event = new EventHandler($db);
    $event->event_id = $_POST['event_id'];
    
    // Update fields
    $event->event_title = $_POST['event_title'];
    $event->event_des = $_POST['event_des'];
    $event->date = $_POST['date'];
    $event->date_started = $_POST['date_started'];
    $event->date_ended = $_POST['date_ended'];
    $event->platform = $_POST['platform'];
    $event->platform_link = $_POST['platform_link'];
    $event->location = $_POST['location'];
    $event->eventvisibility = $_POST['eventvisibility'];

    if ($event->updateEvent()) {
        echo json_encode(['message' => 'Event updated successfully']);
    } else {
        throw new Exception('Update failed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}