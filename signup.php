<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // Generate 6-digit OTP
    $otp = rand(100000, 999999);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email already registered!";
    } else {
        // Insert user (unverified)
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, otp, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone, $role, $otp);

        if ($stmt->execute()) {
            // Send OTP via Email
            include 'send_otp.php';
            if (sendOTP($email, $otp)) {
                // Redirect to OTP verification page
                header("Location: verify_otp.php?email=" . urlencode($email));
                exit();
            } else {
                echo "Failed to send OTP. Please check the error log.";
            }
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Signup</h2>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Phone:</label>
        <input type="text" name="phone" required><br>

        <label>Role:</label>
        <select name="role" required>
            <option value="User">User</option>
            <option value="Admin">Admin</option>
        </select><br>

        <input type="submit" value="Sign Up">
    </form>
    <form method="get" action="login.php">
        <input type="submit" value="Login">
    </form>
</body>
</html>
