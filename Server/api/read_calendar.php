<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debugging function
function debug($message) {
    error_log(date('Y-m-d H:i:s') . " - $message\n", 3, 'debug.log');
}

try {
    require_once(dirname(__FILE__) . '/../core/initialize.php');

    // Check if the database connection is available
    if (!$db) {
        throw new PDOException('Database connection failed');
    }

    // Configure PDO settings
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Instantiate the Calendar class
    $calendar = new Calendar($db);

    // Fetch events from the database
    $result = $calendar->read();
    if (!$result) {
        throw new Exception('Failed to fetch calendar data.');
    }

    $num = $result->rowCount();
    if ($num > 0) {
        $cal_arr = ['data' => []];

        while ($row = $result->fetch()) {
            $cal_arr['data'][] = [
                'calendar_id' => $row['calendar_id'],
                'student_id' => $row['student_id'],
                'event_id' => $row['event_id'],
                'org_id' => $row['org_id'],
                'is_attending' => $row['is_attending'],
                'added_at' => $row['added_at'],
                'event_des' => $row['event_des'],
                'event_title' => $row['event_title'],
                'banner' => base64_encode($row['banner']), // Encode banner image
                'date' => $row['date'],
                'date_started' => $row['date_started'],
                'date_ended' => $row['date_ended'],
                'eventvisibility' => $row['eventvisibility'],
                'platform' => $row['platform'],
                'platform_link' => $row['platform_link'],
                'location' => $row['location'],
                'org_name' => $row['org_name'],
                'org_desc' => $row['org_desc']
            ];
        }

        // Send JSON response with success message
        echo json_encode(['status' => 'success', 'data' => $cal_arr['data']]);
    } else {
        // If no events found, return empty array with success status
        echo json_encode(['status' => 'success', 'data' => []]);
    }
} catch (PDOException $e) {
    // Handle database-related exceptions
    debug("PDOException: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    // Handle general exceptions
    debug("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.', 'error' => $e->getMessage()]);
}
?>
