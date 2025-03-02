<?php
// Database connection (modify with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "book_club";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the book data based on the book_id passed via GET parameter
$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : 1;  // Default book_id is 1

// Get the book details
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book_result = $stmt->get_result();
$book = $book_result->fetch_assoc();

// Get similar books
$similar_books_sql = "SELECT b.id, b.title, b.image_url FROM books b
                      JOIN similar_books sb ON b.id = sb.similar_book_id
                      WHERE sb.book_id = ?";
$similar_books_stmt = $conn->prepare($similar_books_sql);
if ($similar_books_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$similar_books_stmt->bind_param("i", $book_id);
$similar_books_stmt->execute();
$similar_books_result = $similar_books_stmt->get_result();

// Get comments for the book
$comments_sql = "SELECT c.*, u.name FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.book_id = ? AND c.reply_to IS NULL
                 ORDER BY c.created_at DESC";
$comments_stmt = $conn->prepare($comments_sql);
if ($comments_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$comments_stmt->bind_param("i", $book_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Discussion - <?php echo htmlspecialchars($book['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; color: #333; }
        nav { background: #333; padding: 1rem; }
        nav ul { list-style: none; display: flex; justify-content: center; gap: 15px; }
        nav ul li a { color: white; text-decoration: none; font-size: 1.2rem; }
        .container { display: flex; padding: 2rem; gap: 20px; }
        .left-aside, .right-aside { width: 50%; }
        .book-info { background: white; padding: 1rem; border-radius: 5px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); }
        .book-info img { width: 100px; height: 150px; object-fit: cover; border-radius: 5px; }
        .book-info h2 { margin: 10px 0; }
        .similar-books { margin-top: 20px; }
        .similar-books h3 { margin-bottom: 10px; }
        .similar-books .book { display: inline-block; width: 150px; text-align: center; margin-right: 10px; }
        .similar-books img { width: 100px; height: 150px; }
        .comment { background: white; padding: 1rem; margin-bottom: 10px; border-radius: 5px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); }
        .comment .profile { display: flex; gap: 10px; margin-bottom: 10px; }
        .comment .profile img { width: 40px; height: 40px; border-radius: 50%; }
        .comment .comment-text { margin-left: 10px; }
        .comment .comment-text p { margin-bottom: 10px; }
        .thumbs { display: flex; gap: 10px; }
        .thumbs button { background: none; border: none; cursor: pointer; }
        footer { text-align: center; padding: 1rem; background: #333; color: white; margin-top: 20px; }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            <!-- <li><a href="discussion.php">Discussion</a></li> -->
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Left Aside: Book Information and Similar Books -->
        <div class="left-aside">
            <div class="book-info">
                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                <p><a href="<?php echo htmlspecialchars($book['download_link']); ?>">Download</a></p>
            </div>

            <div class="similar-books">
                <h3>Similar Books</h3>
                <?php while ($similar_book = $similar_books_result->fetch_assoc()): ?>
                    <div class="book">
                        <img src="<?php echo htmlspecialchars($similar_book['image_url']); ?>" alt="<?php echo htmlspecialchars($similar_book['title']); ?>">
                        <h4><?php echo htmlspecialchars($similar_book['title']); ?></h4>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Right Aside: User Comments and Interactions -->
        <div class="right-aside">
            <h2>Discussion</h2>
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment">
                    <div class="profile">
                        <img src="default_profile_picture.png" alt="<?php echo htmlspecialchars($comment['name']); ?>"> <!-- Placeholder image -->
                        <div class="comment-text">
                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <div class="thumbs">
                                <button>👍</button>
                                <button>👎</button>
                                <button>🔊</button> <!-- Placeholder for voice recording button -->
                                <button>Reply</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Book Website</p>
    </footer>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
