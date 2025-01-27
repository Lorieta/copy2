<?php
// check_auth.php
session_start();

// Secure session configuration
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Regenerate ID to prevent fixation
if (!isset($_SESSION['created_at'])) {
    session_regenerate_id(true);
    $_SESSION['created_at'] = time();
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

// Session timeout (1 hour)
if (time() - $_SESSION['created_at'] > 3600) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['message' => 'Session expired']);
    exit;
}
?>