<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change as needed
$password = "";      // Change as needed
$dbname = "celestial_jewels";  // Change as needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array('success' => false);

// Get customer ID from request
$customerId = isset($_GET['id']) ? $_GET['id'] : null;

if ($customerId) {
    // Delete customer
    $stmt = $conn->prepare("DELETE FROM customers_tbl WHERE customer_id=?");
    $stmt->bind_param("i", $customerId);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Customer deleted successfully";
    } else {
        $response['message'] = "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response['message'] = "Invalid customer ID";
}

$conn->close();

// Return as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>