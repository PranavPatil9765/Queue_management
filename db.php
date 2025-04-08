<?php
$host = 'localhost';
$dbname = 'queue_db';  // Use your actual database name
$username = 'root';    // Default XAMPP username
$password = '';        // Default XAMPP password is empty

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>