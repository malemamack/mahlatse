<?php
// Include your database connection
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate token and check expiration
    $stmt = $conn->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $reset_token_expiry);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Check if the token is expired
        if (strtotime($reset_token_expiry) > time()) {
            if ($new_password == $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database and reset the token
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();

                echo "Your password has been reset successfully.";
            // Redirect to login page after successful password reset
            header("Location: login.php");
            exit();
        } else {
            echo "Passwords do not match.";
        }
        } else {
            echo "The reset link has expired.";
        }
    } else {
        echo "Invalid reset token.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">

            <label>New Password:</label>
            <input type="password" name="new_password" required><br>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required><br>

            <input type="submit" value="Reset Password">
        </form>
    </div>
</body>
</html>
