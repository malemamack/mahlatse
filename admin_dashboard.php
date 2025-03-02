<?php
session_start();
include('config.php'); // Include your DB connection

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: admin_dashboard.php');
    exit();
}

// Add a new book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'];
    $download_link = $_POST['download_link'];

    $sql = "INSERT INTO books (title, author, category, image_url, download_link) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $title, $author, $category, $image_url, $download_link);
    if ($stmt->execute()) {
        echo "Book added successfully!";
    } else {
        echo "Error adding book: " . $stmt->error;
    }
}

// Edit a book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url'];
    $download_link = $_POST['download_link'];

    $sql = "UPDATE books SET title = ?, author = ?, category = ?, image_url = ?, download_link = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $title, $author, $category, $image_url, $download_link, $book_id);
    if ($stmt->execute()) {
        echo "Book updated successfully!";
    } else {
        echo "Error updating book: " . $stmt->error;
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
$sql = "SELECT * FROM books";
$books_result = $conn->query($sql);

// Fetch all users and activities
$sql_users = "SELECT * FROM users";
$users_result = $conn->query($sql_users);
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
    width: 80%;
    margin: 20px auto;
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

button {
    padding: 10px 20px;
    background-color: #0073e6;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 20px;
}

button:hover {
    background-color: #005bb5;
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

form input, form button {
    padding: 10px;
    margin: 5px 0;
    width: 100%;
    box-sizing: border-box;
}

form button {
    background-color: #4CAF50;
    color: white;
}

form button:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Manage Books</h2>
        <button id="addBookBtn">Add New Book</button>

        <div id="addBookForm" style="display: none;">
            <h3>Add Book</h3>
            <form action="dashboard.php" method="POST">
                <input type="text" name="title" placeholder="Title" required>
                <input type="text" name="author" placeholder="Author" required>
                <input type="text" name="category" placeholder="Category" required>
                <input type="text" name="image_url" placeholder="Image URL" required>
                <input type="text" name="download_link" placeholder="Download Link" required>
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
                    <th>Image URL</th>
                    <th>Download Link</th>
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
                        <td><?php echo $book['image_url']; ?></td>
                        <td><?php echo $book['download_link']; ?></td>
                        <td>
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>">Edit</a> | 
                            <a href="dashboard.php?delete_book=<?php echo $book['id']; ?>">Delete</a>
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
        // Toggle the "Add Book" form visibility
        document.getElementById('addBookBtn').onclick = function() {
            const form = document.getElementById('addBookForm');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        };
    </script>
</body>
</html>
