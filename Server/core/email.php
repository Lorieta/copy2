use SendGrid\Mail\Mail;

class NewsletterEmailer {
    private $conn;
    private $apiKey;
    private $fromEmail;
    private $fromName;

    public function __construct($db, $apiKey, $fromEmail, $fromName) {
        $this->conn = $db;
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function fetchEvents() {
        $query = 'SELECT event_title, event_des, date_started, date_ended, location FROM event_handler';
        $stmt = $this->conn->prepare($query);

        if (!$stmt->execute()) {
            error_log('Failed to fetch events: ' . implode(', ', $stmt->errorInfo()));
            return [];
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendNewsletterEmails() {
        // Fetch subscribers
        $query = 'SELECT foreign_email FROM newsletter';
        $stmt = $this->conn->prepare($query);

        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());
            return;
        }

        // Fetch events to include in the email
        $events = $this->fetchEvents();

        // Create event list HTML
        $eventHtml = '';
        if (!empty($events)) {
            foreach ($events as $event) {
                $eventHtml .= '
                    <div>
                        <h2>' . htmlspecialchars($event['event_title']) . '</h2>
                        <p>' . htmlspecialchars($event['event_des']) . '</p>
                        <p><strong>Start:</strong> ' . htmlspecialchars($event['date_started']) . '</p>
                        <p><strong>End:</strong> ' . htmlspecialchars($event['date_ended']) . '</p>
                        <p><strong>Location:</strong> ' . htmlspecialchars($event['location']) . '</p>
                        <hr>
                    </div>
                ';
            }
        } else {
            $eventHtml = '<p>No upcoming events at this time.</p>';
        }

        // Define the main email HTML content
        $htmlContent = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f8ff;
                    color: #333;
                    text-align: center;
                    padding: 20px;
                }
                h1 {
                    color: #ff6347;
                }
                .event {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #fff;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
            </style>
        </head>
        <body>
            <h1>Welcome to Our Newsletter!</h1>
            <p>Thank you for subscribing. Here are the upcoming events:</p>
            ' . $eventHtml . '
        </body>
        </html>
        ';

        while ($subscriber = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Sending to {$subscriber['foreign_email']}...\n";

            try {
                $email = new Mail();
                $email->setFrom($this->fromEmail, $this->fromName);
                $email->setSubject("Upcoming Events - Newsletter");
                $email->addTo($subscriber['foreign_email']);
                $email->addContent("text/html", $htmlContent);

                $sendgrid = new \SendGrid($this->apiKey);
                $response = $sendgrid->send($email);

                echo "Response Code: " . $response->statusCode() . "\n";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }

            usleep(100000); // Pause to avoid rate-limiting
        }
    }
}
