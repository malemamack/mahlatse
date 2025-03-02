<?php
session_start();
include('config.php'); // Database connection

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: admin_dashboard.php');
    exit();
}

// Handle file upload function
function uploadFile($file, $folder, $allowedTypes) {
    $targetDir = "uploads/$folder/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $targetFilePath;
        }
    }
    return null;
}

// Add a new book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];

    $imagePath = uploadFile($_FILES['image'], "images", ["jpg", "jpeg", "png"]);
    $pdfPath = uploadFile($_FILES['pdf'], "pdfs", ["pdf"]);

    if ($imagePath && $pdfPath) {
        $sql = "INSERT INTO books (title, author, category, image_path, pdf_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $title, $author, $category, $imagePath, $pdfPath);

        if ($stmt->execute()) {
            echo "Book added successfully!";
        } else {
            echo "Error adding book: " . $stmt->error;
        }
    } else {
        echo "File upload failed. Ensure you're uploading valid images and PDFs.";
    }
}

// Delete a book
if (isset($_GET['delete_book'])) {
    $book_id = $_GET['delete_book'];
    $sql = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        echo "Book deleted successfully!";
    } else {
        echo "Error deleting book: " . $stmt->error;
    }
}

// Fetch all books
$books_result = $conn->query("SELECT * FROM books");

// Fetch all users and activities
$users_result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        button {
            padding: 10px;
            background-color: #0073e6;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #005bb5;
        }
        form input, form button {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
        form button {
            background-color: #4CAF50;
        }
        form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <nav><a href="logout.php">Logout</a></nav>
</header>

<div class="container">
    <h2>Manage Books</h2>
    <button id="addBookBtn">Add New Book</button>

    <div id="addBookForm" style="display: none;">
        <h3>Add Book</h3>
        <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author" required>
            <input type="text" name="category" placeholder="Category" required>
            <label>Upload Image (JPG, PNG):</label>
            <input type="file" name="image" accept=".jpg, .jpeg, .png" required>
            <label>Upload PDF:</label>
            <input type="file" name="pdf" accept=".pdf" required>
            <button type="submit" name="add_book">Add Book</button>
        </form>
    </div>

    <h3>Books List</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Image</th>
                <th>PDF</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($book = $books_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $book['id']; ?></td>
                    <td><?php echo $book['title']; ?></td>
                    <td><?php echo $book['author']; ?></td>
                    <td><?php echo $book['category']; ?></td>
                    <td><img src="<?php echo $book['image_path']; ?>" alt="Book Image" width="50"></td>
                    <td><a href="<?php echo $book['pdf_path']; ?>" target="_blank">Download PDF</a></td>
                    <td>
                        <a href="admin_dashboard.php?delete_book=<?php echo $book['id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Manage Users & Activities</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Last Activity</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['last_activity']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('addBookBtn').onclick = function() {
        document.getElementById('addBookForm').style.display = 'block';
    };
</script>

</body>
</html>
