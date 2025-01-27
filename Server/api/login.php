<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once(dirname(__FILE__) . '/../core/initialize.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Capture and decode the raw input
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput);

        // Debug: Log the raw input and the decode result
        error_log("Raw input: " . $rawInput);
        error_log("Decoded input: " . json_encode($data));

        // Check if json_decode() failed
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON input']);
            error_log("JSON Decode Error: " . json_last_error_msg());
            exit();
        }

        // Validate if email and password are provided
        if (!empty($data->email) && !empty($data->password)) {
            $user = new UserHandler($db);
            $result = $user->login($data->email, $data->password);

            // Debug: Log the login result
            error_log("Login result: " . json_encode($result));

            if ($result && is_array($result)) {
                // Store session information
                $_SESSION['logged_in'] = true;
                $_SESSION['student_id'] = $result['student_id'];
                $_SESSION['account_type'] = $result['account_type'];
                $_SESSION['org_ids'] = isset($result['org_ids']) ? $result['org_ids'] : [$result['org_id']]; // Handle both single org_id and array of org_ids

                // Debug: Log the session data
                error_log("Session data: " . json_encode($_SESSION));

                // If the user is an Admin
                if ($result['account_type'] === 'Admin') {
                    http_response_code(200);
                    echo json_encode([
                        'message' => 'Welcome, Admin!',
                        'user' => [
                            'student_id' => $result['student_id'],
                            'firstname' => $result['firstname'],
                            'lastname' => $result['lastname'],
                            'email' => $result['email'],
                            'account_type' => $result['account_type'],
                            'org_id' => $result['org_id'] // Only a single org_id for Admin
                        ]
                    ]);
                } else {
                    http_response_code(200);
                    echo json_encode([
                        'message' => 'Login successful',
                        'user' => [
                            'student_id' => $result['student_id'],
                            'firstname' => $result['firstname'],
                            'lastname' => $result['lastname'],
                            'email' => $result['email'],
                            'account_type' => $result['account_type'],
                            'org_ids' => $result['org_ids'] // Array of org_ids for regular users
                        ]
                    ]);
                }

            } else {
                // Handle invalid login (either failed login or unexpected result)
                http_response_code(401);
                echo json_encode(['message' => 'Invalid email or password']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Email and password are required']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Server error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Server error: ' . $e->getMessage()]);
}
?>
