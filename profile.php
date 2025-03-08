<?php
// filepath: c:\xampp\htdocs\mahlatse\profile.php
// Start session and check if user is logged in
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('config.php'); // Include your DB connection

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $first_name = $_POST['name'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = $_FILES['profile_picture'];
        $image_name = time() . '_' . basename($profile_picture['name']);
        $target_dir = 'uploads/profile_pics/';
        $target_file = $target_dir . $image_name;

        // Check if the uploads directory exists, if not, create it
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB limit

        if (!in_array($profile_picture['type'], $allowed_types)) {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            $profile_picture = $user['profile_picture']; // Keep the old image if validation fails
        } elseif ($profile_picture['size'] > $max_file_size) {
            $_SESSION['error_message'] = "File size exceeds the maximum allowed limit of 5MB.";
            $profile_picture = $user['profile_picture']; // Keep the old image if validation fails
        } else {
            // Check if the file is an actual image
            $check = getimagesize($profile_picture['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
                    $profile_picture = $image_name; // Store the new file name
                } else {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                    $profile_picture = $user['profile_picture']; // Keep the old image if upload fails
                }
            } else {
                $_SESSION['error_message'] = "File is not an image.";
                $profile_picture = $user['profile_picture']; // Keep the old image if file is not an image
            }
        }
    } else {
        $profile_picture = $user['profile_picture']; // Keep the old image if no new image is uploaded
    }

    // Update user details in the database
    $update_sql = "UPDATE users SET name = ?, email = ?, bio = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $first_name, $email, $bio, $profile_picture, $user_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php"); // Redirect to the same page to show the message
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . $stmt->error;
        header("Location: profile.php"); // Redirect back to the profile page
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        .container {
            width: 50%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .profile-image-preview {
            max-width: 150px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #005bb5;
        }

        nav {
            background: #333;
            padding: 1rem;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }

        nav ul li a:hover {
            background-color: #555;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <header>
        <h1>Edit Your Profile</h1>
    </header>

    <div class="container">
        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">First Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio" rows="4" required><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture">
                <img src="uploads/profile_pics/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-image-preview">
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
