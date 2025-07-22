<?php
session_start();
include "../../Config/db.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


$email = $_POST['email'];
$_SESSION['reset_email'] = $email;
$query = $conn->prepare("SELECT * FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "Email not registered.";
    exit;
}

$otp = rand(100000, 999999); 
$_SESSION['otp'] = $otp;
$_SESSION['email'] = $email;
$mail = new phpmailer(true);
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; 
$mail->SMTPAuth = true;
$mail->Username = $_ENV['GMAIL_USERNAME'];
$mail->Password = $_ENV['GMAIL_APP_PASSWORD'];
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('thezulekhacollection@gmail.com', 'E - Books');
// Always validate email before using
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mail->addAddress($email);
} else {
    die("Invalid or missing email address.");
}

$mail->Subject = 'Your OTP Code';
$mail->isHTML(true); // This is important for HTML formatting
$mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - E - Books</title>
</head>
<body style="margin: 0; padding: 0; background-color: #020617; font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #cbd5e1;">
    <table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%; min-height: 100vh; background-color: #020617; padding: 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%; background-color: #0f172a; border: 1px solid #334155; border-radius: 16px; padding: 24px; text-align: center;">
                    <!-- Header -->
                    <tr>
                        <td>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td>
                                        <img src="https://images.unsplash.com/photo-1512820790803-83ca3b5d78b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=64&h=64&q=80" alt="Book Icon" style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%;">
                                    </td>
                                </tr>
                            </table>
                            <h1 style="font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; font-weight: 800; font-size: 30px; color: #34d399; margin: 0 0 8px;">E - Books</h1>
                            <div style="width: 100px; height: 2px; background: linear-gradient(to right, transparent, #10b981, transparent); margin: 24px auto;"></div>
                        </td>
                    </tr>
                    
                    <!-- Greeting -->
                    <tr>
                        <td style="text-align: center; margin-bottom: 32px;">
                            <h2 style="font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; font-weight: 600; font-size: 24px; color: #ffffff; margin: 0 0 8px;">Welcome to Our Community!</h2>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="text-align: center; margin-bottom: 32px;">
                            <p style="font-size: 16px; color: #cbd5e1; margin: 0 0 24px; line-height: 1.5;">
                                Thank you for joining <span style="color: #34d399;">E - Books</span>. We're excited to have you on board! To complete your registration and secure your account, please use the verification code below.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto 24px; background-color: #1e293b; border: 2px solid #334155; border-radius: 12px; padding: 20px;">
                                <tr>
                                    <td style="font-size: 14px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">Your Verification Code</td>
                                </tr>
                                <tr>
                                    <td style="font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif; font-size: 32px; font-weight: 600; color: #ffffff; background-color: #10b981; padding: 15px 25px; border-radius: 8px; letter-spacing: 6px;">
                                        $otp
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto 24px; background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 15px;">
                                <tr>
                                    <td style="font-size: 14px; color: #94a3b8;">
                                        This code will expire in <span style="color: #f87171;">10 minutes</span>. Please use it promptly.
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto 24px; background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 15px;">
                                <tr>
                                    <td style="font-size: 14px; color: #94a3b8;">
                                        🔒 For your security, never share this code with anyone. Our team will never ask for your verification code.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Divider -->
                    <tr>
                        <td>
                            <div style="width: 100px; height: 2px; background: linear-gradient(to right, transparent, #10b981, transparent); margin: 24px auto;"></div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="text-align: center; color: #64748b; font-size: 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto 16px;">
                                <tr>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="display: inline-block; text-decoration: none;">
                                            <img src="https://img.icons8.com/ios-filled/24/64748b/facebook.png" alt="Facebook" style="width: 24px; height: 24px;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="display: inline-block; text-decoration: none;">
                                            <img src="https://img.icons8.com/ios-filled/24/64748b/twitter.png" alt="Twitter" style="width: 24px; height: 24px;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="display: inline-block; text-decoration: none;">
                                            <img src="https://img.icons8.com/ios-filled/24/64748b/instagram-new.png" alt="Instagram" style="width: 24px; height: 24px;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0;">© 2025 E - Books. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;


if ($mail->send()) {
    header("Location: ../enter-otp.php");
} else {
    echo "OTP send failed. Try again.";
}
