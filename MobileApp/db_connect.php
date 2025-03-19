<?php
// Database connection configuration
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "celestial_jewels"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to UTF-8
$conn->set_charset("utf8mb4");
?>