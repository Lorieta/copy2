<?php
// send_newsletter.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization');

require_once(dirname(__FILE__) . '/../core/initialize.php'); // Database setup
require 'C:\laragon\www\OrgSync-Website\vendor\autoload.php'; // SendGrid library

$logFile = dirname(__FILE__) . '/debug.log';
function logDebug($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Fetch events
    $queryEvents = 'SELECT event_title, event_des, date, date_started, date_ended, 
                    eventvisibility, platform, platform_link, location 
                    FROM event_handler 
                    WHERE eventvisibility = "public"';
    $stmtEvents = $db->prepare($queryEvents);
    $stmtEvents->execute();
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

    logDebug("Fetched " . count($events) . " public events from the database.");

    if (!$events) {
        logDebug("No public events found.");
        http_response_code(200);
        echo json_encode(['message' => 'No public events to send']);
        exit;
    }

    // Fetch subscribers
    $querySubscribers = 'SELECT foreign_email FROM newsletter';
    $stmtSubscribers = $db->prepare($querySubscribers);
    $stmtSubscribers->execute();
    $subscribers = $stmtSubscribers->fetchAll(PDO::FETCH_COLUMN);

    logDebug("Fetched " . count($subscribers) . " subscribers from the database.");

    if (!$subscribers) {
        logDebug("No subscribers found.");
        http_response_code(200);
        echo json_encode(['message' => 'No subscribers to send the newsletter']);
        exit;
    }

    // Generate email content
    $eventDetails = '';
    foreach ($events as $event) {
        $eventDetails .= "
        <div class='event'>
            <h2>{$event['event_title']}</h2>
            <p>{$event['event_des']}</p>
            <p><strong>Date:</strong> {$event['date']}</p>
            <p><strong>Time:</strong> {$event['date_started']} - {$event['date_ended']}</p>
            <p><strong>Location:</strong> {$event['location']}</p>
            <p><strong>Platform:</strong> {$event['platform']} (<a href='{$event['platform_link']}'>Join Now</a>)</p>
        </div>";
    }

    $htmlContent = "
    <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #fff;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                h1 {
                    text-align: center;
                    color: maroon;
                    font-size: 32px;
                    margin-bottom: 20px;
                }
                h2 {
                    color: maroon;
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                p {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #333;
                }
                strong {
                    color: maroon;
                }
                a {
                    color: maroon;
                    text-decoration: none;
                    font-weight: bold;
                }
                .event {
                    background-color: #f9f9f9;
                    border-left: 5px solid maroon;
                    margin-bottom: 20px;
                    padding: 20px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    border-radius: 8px;
                }
                .event hr {
                    border: 0;
                    border-top: 1px solid #eee;
                    margin: 20px 0;
                }
                footer {
                    text-align: center;
                    font-size: 14px;
                    color: #777;
                    margin-top: 30px;
                }
                footer a {
                    color: maroon;
                }
            </style>
        </head>
        <body>
            <h1>Upcoming Events - Don't Miss Out!</h1>
            {$eventDetails}
            <footer>
                <p>You're receiving this email because you subscribed to our newsletter. <a href='#'>Unsubscribe</a> at any time.</p>
            </footer>
        </body>
    </html>";

    // Initialize SendGrid
    
    $sendGrid = new \SendGrid($sendGridApiKey);

    // Send email to each subscriber
    foreach ($subscribers as $email) {
        $emailContent = new \SendGrid\Mail\Mail();
        $emailContent->setFrom("johncarlo.lorieta@gmail.com", "Orgsync");
        $emailContent->setSubject("Upcoming Events Newsletter");
        $emailContent->addTo($email);
        $emailContent->addContent("text/html", $htmlContent);

        try {
            $response = $sendGrid->send($emailContent);
            if ($response->statusCode() >= 400) {
                logDebug("Failed to send email to {$email}: " . $response->body());
            } else {
                logDebug("Email sent successfully to {$email} with status code " . $response->statusCode());
            }
        } catch (Exception $e) {
            logDebug("Error sending email to {$email}: " . $e->getMessage());
        }
    }

    logDebug("Newsletter sending process completed.");
    http_response_code(200);
    echo json_encode(['message' => 'Newsletter sent successfully']);
} catch (Exception $e) {
    logDebug("Error occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Error occurred', 'error' => $e->getMessage()]);
}
?>
