<?php
// Database connection
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
    // Get the comment ID and user ID
    $comment_id = $_POST['comment_id'];
    $user_id = 1; // Replace with the actual logged-in user's ID

    // Check if the user already liked this comment
    $check_like_sql = "SELECT * FROM comment_likes WHERE comment_id = ? AND user_id = ?";
    $check_like_stmt = $conn->prepare($check_like_sql);
    $check_like_stmt->bind_param("ii", $comment_id, $user_id);
    $check_like_stmt->execute();
    $check_like_result = $check_like_stmt->get_result();

    if ($check_like_result->num_rows > 0) {
        // User already liked the comment, remove the like
        $delete_like_sql = "DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?";
        $delete_like_stmt = $conn->prepare($delete_like_sql);
        $delete_like_stmt->bind_param("ii", $comment_id, $user_id);
        $delete_like_stmt->execute();

        // Decrement the like count
        $update_like_count_sql = "UPDATE comments SET like_count = like_count - 1 WHERE id = ?";
        $update_like_count_stmt = $conn->prepare($update_like_count_sql);
        $update_like_count_stmt->bind_param("i", $comment_id);
        $update_like_count_stmt->execute();
    } else {
        // User hasn't liked the comment yet, add a new like
        $insert_like_sql = "INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)";
        $insert_like_stmt = $conn->prepare($insert_like_sql);
        $insert_like_stmt->bind_param("ii", $comment_id, $user_id);
        $insert_like_stmt->execute();

        // Increment the like count
        $update_like_count_sql = "UPDATE comments SET like_count = like_count + 1 WHERE id = ?";
        $update_like_count_stmt = $conn->prepare($update_like_count_sql);
        $update_like_count_stmt->bind_param("i", $comment_id);
        $update_like_count_stmt->execute();
    }

    // Get the updated like count and return it
    $get_like_count_sql = "SELECT like_count FROM comments WHERE id = ?";
    $get_like_count_stmt = $conn->prepare($get_like_count_sql);
    $get_like_count_stmt->bind_param("i", $comment_id);
    $get_like_count_stmt->execute();
    $get_like_count_result = $get_like_count_stmt->get_result();
    $like_count = $get_like_count_result->fetch_assoc()['like_count'];

    echo $like_count; // Return the new like count to the frontend

    $conn->close();
}
?>
