<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows requests from any domain
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Config error.']);
    exit;
}

$host = 'localhost';
$user = 'abeksyst_admin';
$pass = '1PiY#r8DY(cy63'; 
$db   = 'abeksyst_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    $db_saved = $stmt->execute();
    $stmt->close();

    if ($db_saved) {
        $email_sent = false;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->Port       = $_ENV['SMTP_PORT'];
            
            if ($mail->Port == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Timeout = 15;

            // --- ADMIN NOTIFICATION ---
            $mail->setFrom($_ENV['SMTP_USER'], 'Abek Systems Website');
            $mail->addAddress($_ENV['ADMIN_EMAIL']);
            $mail->addReplyTo($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "New Inquiry: $name";
            $mail->Body    = "
                <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #061d2b;'>New Website Inquiry</h2>
                    <hr>
                    <p><strong>Customer Name:</strong> $name</p>
                    <p><strong>Customer Email:</strong> $email</p>
                    <p><strong>Message Details:</strong></p>
                    <div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                </div>
            ";
            $mail->send();

            // --- USER CONFIRMATION ---
            $mail->clearAddresses();
            $mail->clearReplyTos();
            $mail->addAddress($email);
            $mail->addReplyTo($_ENV['ADMIN_EMAIL'], 'Abek Systems');
            
            $mail->Subject = "We've Received Your Message - Abek Systems";
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #061d2b; padding: 30px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>Message Received</h1>
                        <p style='color: #00bfff; margin: 5px 0 0 0;'>Thank you for reaching out to us!</p>
                    </div>
                    
                    <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                        <p>Hello <strong>$name</strong>,</p>
                        <p>This is to confirm that we have successfully received your inquiry through our website. Our team of experts is currently reviewing your message and will get back to you within <strong>24 business hours</strong>.</p>
                        
                        <div style='background-color: #f8f9fa; border-left: 4px solid #00bfff; padding: 20px; margin: 25px 0;'>
                            <h3 style='margin-top: 0; color: #061d2b; font-size: 16px;'>Summary of your inquiry:</h3>
                            <p style='font-style: italic; margin-bottom: 0;'>\"" . nl2br(htmlspecialchars($message)) . "\"</p>
                        </div>
                        
                        <p>In the meantime, feel free to explore our latest services or follow us on our social media channels for updates.</p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='http://abeksystems.com/#services' style='background-color: #061d2b; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Explore Our Services</a>
                        </div>
                    </div>
                    
                    <div style='background-color: #f4f4f4; padding: 20px; text-align: center; color: #777777; font-size: 12px;'>
                        <p><strong>Abek Systems Ltd</strong><br>30 Abacha Road, GRA, Port Harcourt, Nigeria</p>
                        <p>&copy; 2026 Abek Systems. All rights reserved.</p>
                        <p>If you didn't submit this form, please ignore this email.</p>
                    </div>
                </div>
            ";
            $mail->send();
            $email_sent = true;
        } catch (Exception $e) {
            file_put_contents('mail_error.log', "[" . date('Y-m-d H:i:s') . "] " . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
        }

        if ($email_sent) {
            echo json_encode(['status' => 'success', 'message' => 'Message sent! A confirmation has been sent to your email.']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Message received! Our team will contact you soon.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
}
$conn->close();
