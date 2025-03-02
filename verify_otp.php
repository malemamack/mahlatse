<?php
include 'config.php';
include 'send_otp.php'; // Include the send_otp function

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resend'])) {
        // Resend OTP
        $email = $_POST['email'];
        $otp = rand(100000, 999999); // Generate a new OTP

        // Update the OTP in the database
        $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ?");
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            echo "Failed to prepare statement. Please check the error log.";
            exit;
        }
        $stmt->bind_param("ss", $otp, $email);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo "Failed to execute statement. Please check the error log.";
            exit;
        }

        // Send the new OTP
        if (sendOTP($email, $otp)) {
            echo "A new OTP has been sent to your email.";
        } else {
            echo "Failed to send OTP. Please check the error log.";
        }
    } else {
        // Verify OTP
        $email = $_POST['email'];
        $otp = $_POST['otp'];

        // Check OTP
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp = ?");
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            echo "Failed to prepare statement. Please check the error log.";
            exit;
        }
        $stmt->bind_param("ss", $email, $otp);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            echo "Failed to execute statement. Please check the error log.";
            exit;
        }
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update user as verified
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE email = ?");
            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                echo "Failed to prepare statement. Please check the error log.";
                exit;
            }
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                echo "Failed to execute statement. Please check the error log.";
                exit;
            }

            echo "Account verified! <a href='login.php'>Login Here</a>";
        } else {
            echo "Invalid OTP. Try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Verify OTP</h2>
    <form method="post">
        <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
        <label>Enter OTP:</label>
        <input type="text" name="otp" required><br>

        <input type="submit" value="Verify">
    </form>
    <form method="post">
        <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
        <input type="submit" name="resend" value="Resend OTP">
    </form>
</body>
</html>
