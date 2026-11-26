<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/html; charset=UTF-8');

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Load PHPMailer
require __DIR__ . '/../vendor/autoload.php';

// Receive form data
$fullName = trim($_POST['Full name'] ?? '');
$phone    = trim($_POST['Mobile phone'] ?? '');
$email    = trim($_POST['Email'] ?? '');
$service  = trim($_POST['Service'] ?? '');
$time     = trim($_POST['Best time'] ?? '');
$patient  = trim($_POST['Patient type'] ?? '');

$sms       = isset($_POST['Transactional SMS consent']) ? 'Yes' : 'No';
$marketing = isset($_POST['Promotional SMS consent']) ? 'Yes' : 'No';

// Load email template
$templatePath = __DIR__ . '/../templates/appointment.html';

if (!file_exists($templatePath)) {
    http_response_code(500);
    exit('Email template not found.');
}

$template = file_get_contents($templatePath);

// Replace template variables
$body = str_replace(
    [
        '{{fullName}}',
        '{{phone}}',
        '{{email}}',
        '{{service}}',
        '{{time}}',
        '{{patient}}',
        '{{sms}}',
        '{{marketing}}'
    ],
    [
        htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($time, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($patient, ENT_QUOTES, 'UTF-8'),
        $sms,
        $marketing
    ],
    $template
);

$mail = new PHPMailer(true);

try {

    // SMTP Configuration
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'checkbox.webd@gmail.com';
    $mail->Password   = 'vftnirhrjepdlpbj';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    // /vftn irhr jepd lpbj

    // Sender
    $mail->setFrom(
        'info@caresmiledentist.com',
        'Care Smile Dental'
    );

    // Recipients
    $mail->addAddress('info@caresmiledentist.com');
    $mail->addAddress('Prashant102@yahoo.com');
    $mail->addAddress('Neelnaidu@gmail.com');
    $mail->addAddress('checkbox.webd@gmail.com');

    // Reply to customer
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $fullName);
    }

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Dental Appointment Request';
    $mail->Body    = $body;

    $mail->AltBody =
        "New Appointment Request\n\n" .
        "Name: $fullName\n" .
        "Phone: $phone\n" .
        "Email: $email\n" .
        "Service: $service\n" .
        "Preferred Time: $time\n" .
        "Patient Type: $patient\n" .
        "Transactional SMS Consent: $sms\n" .
        "Promotional SMS Consent: $marketing\n";

    // Send email
    $mail->send();

    // Redirect after successful submission
    header('Location: ../thank-you.html');
    exit;
} catch (Exception $e) {

    error_log('Appointment Email Error: ' . $mail->ErrorInfo);

    http_response_code(500);
    echo 'Sorry, your appointment request could not be sent. Please try again.';
}
