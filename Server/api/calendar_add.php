<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');
session_start();

function debug($message, $level = 'INFO', $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [{$level}] " . $message;
    if (!empty($context)) $log_entry .= " | " . json_encode($context);
    error_log($log_entry . "\n", 3, 'calendar_debug.log');
}

try {
    debug('Calendar Add API Endpoint Started', 'START', [
        'remote_ip' => $_SERVER['REMOTE_ADDR'],
        'request_method' => $_SERVER['REQUEST_METHOD']
    ]);

    require_once(dirname(__FILE__) . '/../core/initialize.php');
    
    // Debug raw input
    $raw_input = file_get_contents('php://input');
    debug('Raw Input Received', 'INPUT', ['raw_data' => $raw_input]);
    
    $data = json_decode($raw_input, true);
    
    // Debug parsed input
    debug('Parsed Input Data', 'PARSE', [
        'input_data' => $data,
        'parse_success' => json_last_error() === JSON_ERROR_NONE
    ]);

    // Validate required fields
    $required_fields = ['event_id', 'student_id', 'org_id'];
    $missing_fields = array_diff($required_fields, array_keys($data));
    
    if (!empty($missing_fields)) {
        debug('Validation Failed: Missing Fields', 'VALIDATION', [
            'missing_fields' => $missing_fields,
            'received_keys' => array_keys($data)
        ]);
        http_response_code(400);
        throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
    }

    // Additional input validation
    debug('Input Validation Checks', 'VALIDATE', [
        'event_id_type' => gettype($data['event_id']),
        'student_id_type' => gettype($data['student_id']),
        'org_id_type' => gettype($data['org_id'])
    ]);

    // Check existing participation
    $calendar = new Calendar($db);
    $existing_participation = $calendar->exists($data['student_id'], $data['event_id'], $data['org_id']);
    
    debug('Participation Check', 'CHECK', [
        'student_id' => $data['student_id'],
        'event_id' => $data['event_id'],
        'org_id' => $data['org_id'],
        'already_exists' => $existing_participation
    ]);

    if ($existing_participation) {
        debug('Duplicate join attempt', 'VALIDATION', $data);
        http_response_code(409);
        throw new Exception('You have already joined this event');
    }

    // Prepare data
    $calendar_data = [
        'event_id' => (int)$data['event_id'],
        'student_id' => $data['student_id'],
        'org_id' => (int)$data['org_id'],
        'is_attending' => true,
        'added_at' => date('Y-m-d H:i:s'),
        'visibility_status' => 'public'
    ];

    debug('Prepared Calendar Data', 'PREPARE', $calendar_data);

    // Add to calendar
    $add_result = $calendar->add($calendar_data);
    
    debug('Calendar Add Operation', 'RESULT', [
        'success' => $add_result,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    if ($add_result) {
        http_response_code(201);
        echo json_encode(['message' => 'Event added successfully']);
    } else {
        throw new Exception('Failed to add event');
    }

} catch (Exception $e) {
    debug('Error Caught', 'ERROR', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    debug('Calendar Add API Endpoint Completed', 'END', [
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>