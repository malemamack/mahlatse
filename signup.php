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

    // Handle profile picture upload
    $profile_picture = 'default_profile_picture.png'; // Default profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/profile_pictures/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_picture = $target_dir . time() . '_' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email already registered!";
    } else {
        // Insert user (unverified)
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, otp, is_verified, profile_picture) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->bind_param("sssssss", $name, $email, $password, $phone, $role, $otp, $profile_picture);

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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('img/login.jpg'); /* Replace with the path to your background image */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"], select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
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
        .login-link {
            text-align: center;
            margin-top: 10px;
        }
        .login-link a {
            color: #0073e6;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Signup</h2>
        <form method="post" enctype="multipart/form-data">
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

            <label>Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*"><br>

            <input type="submit" value="Sign Up">
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
