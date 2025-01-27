<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Start session
session_start();
session_unset(); // Unset session variables
session_destroy(); // Destroy the session

http_response_code(200);
echo json_encode(['message' => 'Logged out successfully']);
