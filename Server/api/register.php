<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Initialize the database connection and UserHandler class
require_once(dirname(__FILE__) . '/../core/initialize.php');

// Retrieve the POST data from the body
$data = json_decode(file_get_contents("php://input"));

if (isset($data->studentid) && isset($data->firstname) && isset($data->lastname) && isset($data->email) && isset($data->password)) {
    // Create a new instance of the UserHandler class
    $post = new UserHandler($db);

    // Assign the data to the UserHandler object
    $post->student_id = $data->studentid;
    $post->firstname = $data->firstname;
    $post->lastname = $data->lastname;
    $post->email = $data->email;
    $post->password = $data->password;
    
    $post->account_type = $data->account_type;

    // Create the user
    if ($post->create()) {
        echo json_encode(array('message' => 'User Created'));
    } else {
        echo json_encode(array('message' => 'User Not Created'));
    }
} else {
    echo json_encode(array('message' => 'Missing required fields'));
}
?>
