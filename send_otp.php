<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change if needed
        $mail->SMTPAuth = true;
        $mail->Username = 'malemamahlatse70@gmail.com'; // Change to your email
        $mail->Password = 'zxuj zmye huln rnrz'; // Use App Password if using Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@yourdomain.com', 'Mahlatses Cyber Hub');
        $mail->addAddress($email);

        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP Code is: $otp";

        // Enable verbose debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'error_log';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("OTP Email error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
