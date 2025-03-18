<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change as needed
$password = "";      // Change as needed
$dbname = "celestial_jewelry";  // Change as needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get customer ID from request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prepare and execute query
$stmt = $conn->prepare("SELECT customer_id, username, email, phone_number, address_id FROM customers_tbl WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$customer = array();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customer = array(
        'customer_id' => $row['customer_id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'phone_number' => $row['phone_number'],
        'address' => '' // We'll leave this empty for now since we don't have an address table
    );
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($customer);

$stmt->close();
$conn->close();
?>