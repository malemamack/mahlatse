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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = 1; // Replace with actual logged-in user ID
    $book_id = $_POST['book_id'];
    $comment = trim($_POST['comment']);
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : null;

    if (!empty($comment)) {
        $insert_comment_sql = "INSERT INTO comments (book_id, user_id, comment, reply_to, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insert_comment_stmt = $conn->prepare($insert_comment_sql);
        if ($insert_comment_stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $insert_comment_stmt->bind_param("iisi", $book_id, $user_id, $comment, $reply_to);
        $insert_comment_stmt->execute();
        $insert_comment_stmt->close();
        
        // Refresh page to display new comment or reply
        header("Location: ".$_SERVER['PHP_SELF']."?book_id=".$book_id);
        exit();
    }
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
$similar_books_sql = "SELECT b.id, b.title, b.image_path FROM books b
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
$comments_sql = "SELECT c.*, u.name, u.profile_picture FROM comments c
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

// Fetch replies for each comment
$replies_sql = "SELECT c.*, u.name, u.profile_picture FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.reply_to = ? ORDER BY c.created_at ASC";
$replies_stmt = $conn->prepare($replies_sql);
if ($replies_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
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
        .book-info form {
            margin-top: 10px;
        }
        .book-info textarea {
            width: 100%;
            height: 60px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .book-info button {
            background: #333;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-top: 5px;
            cursor: pointer;
        }
        .book-info button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="user_dashboard.php">Home</a></li>
            
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Left Aside: Book Information and Similar Books -->
        <div class="left-aside">
            <div class="book-info">
                <img src="<?php echo htmlspecialchars($book['image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                <p><a href="<?php echo htmlspecialchars($book['pdf_path']); ?>" class="download-btn">Download</a></p>
                
                <!-- Comment Form -->
                <form action="" method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                    <textarea name="comment" placeholder="Write your comment..." required></textarea>
                    <button type="submit" name="submit_comment">Comment</button>
                </form>
            </div>
        </div>

        <!-- Right Aside: User Comments and Interactions -->
        <div class="right-aside">
            <h2>Discussion</h2>
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment">
                    <div class="profile">
                        <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="<?php echo htmlspecialchars($comment['name']); ?>" width="40" height="40"> <!-- User's profile picture -->
                        <div class="comment-text">
                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <button class="reply-button" onclick="showReplyForm(<?php echo $comment['id']; ?>)">Reply</button>
                            <button class="like-button" onclick="likeComment(<?php echo $comment['id']; ?>)">Like</button>
                            <span id="like-count-<?php echo $comment['id']; ?>"><?php echo $comment['like_count']; ?></span>
                        </div>
                    </div>
                    <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form" style="display:none;">
                        <form action="" method="POST">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <input type="hidden" name="reply_to" value="<?php echo $comment['id']; ?>">
                            <textarea name="comment" placeholder="Write your reply..." required></textarea>
                            <button type="submit" name="submit_reply">Reply</button>
                        </form>
                    </div>
                    <?php
                    // Fetch and display replies for this comment
                    $replies_stmt->bind_param("i", $comment['id']);
                    $replies_stmt->execute();
                    $replies_result = $replies_stmt->get_result();
                    while ($reply = $replies_result->fetch_assoc()): ?>
                        <div class="comment reply">
                            <div class="profile">
                                <img src="<?php echo htmlspecialchars($reply['profile_picture']); ?>" alt="<?php echo htmlspecialchars($reply['name']); ?>" width="40" height="40"> <!-- User's profile picture -->
                                <div class="comment-text">
                                    <strong><?php echo htmlspecialchars($reply['name']); ?></strong>
                                    <p><?php echo htmlspecialchars($reply['comment']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Book Website</p>
    </footer>

    <script>
        function showReplyForm(commentId) {
            var replyForm = document.getElementById('reply-form-' + commentId);
            if (replyForm.style.display === 'none') {
                replyForm.style.display = 'block';
            } else {
                replyForm.style.display = 'none';
            }
        }

        function likeComment(commentId) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "like_comment.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var likeCount = document.getElementById('like-count-' + commentId);
                likeCount.innerText = xhr.responseText; // Update like count
            }
        };
        xhr.send("comment_id=" + commentId); // Send comment_id to the backend
    }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
