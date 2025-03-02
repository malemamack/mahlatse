<?php
$servername = "localhost";
$username = "root"; // Change if using another username
$password = ""; // Change if you have a database password
$dbname = "book_club"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
