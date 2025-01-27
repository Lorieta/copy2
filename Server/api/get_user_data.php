<?php
// get_user_data.php
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');


require_once(dirname(__FILE__) . '/../core/initialize.php');

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Not logged in']);
    exit;
}

$user = new UserHandler($db);
$user->student_id = $_SESSION['student_id'];
$user->read_single();

echo json_encode([
    'firstname' => $user->firstname,
    'lastname' => $user->lastname,
    'email' => $user->email,
    'account_type' => $user->account_type
]);