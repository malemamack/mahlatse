<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

include 'config.php';  // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_email);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Generate a unique reset token and expiry time
        $reset_token = bin2hex(random_bytes(32));
        $reset_token_expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));  // Token expires in 1 hour

        // Update the database with the reset token and expiry
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $reset_token, $reset_token_expiry, $user_id);
        $update_stmt->execute();

        // Send password reset email
        $reset_link = "http://localhost/mahlatse/reset_password.php?token=$reset_token";  // Change to your reset password page URL

        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // SMTP server for Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'malemamahlatse70@gmail.com';  // Your Gmail email address
            $mail->Password = 'zxuj zmye huln rnrz';  // Your Gmail password or app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('your-email@gmail.com', 'Your Name');
            $mail->addAddress($user_email, 'User');  // Send to user's email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "You requested a password reset. Click the link below to reset your password:<br><br>
                              <a href='$reset_link'>$reset_link</a><br><br>
                              This link will expire in 1 hour.";

            $mail->send();
            echo 'A password reset link has been sent to your email.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo 'No account found with that email address.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #005bb5;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #0073e6;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Forgot Password</h2>
        <form action="forgot_password.php" method="POST">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" required placeholder="Your email address">
            <input type="submit" value="Reset Password">
        </form>
        <a href="login.php">Back to login</a>
    </div>

</body>
</html>

