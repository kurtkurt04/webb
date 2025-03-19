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

// Get customer ID from request
$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = array('success' => false);

if ($customerId > 0) {
    // Get customer details
    $stmt = $conn->prepare("SELECT customer_id, username, email, phone_number FROM customers_tbl WHERE customer_id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $response['success'] = true;
        $response['customer'] = $result->fetch_assoc();
    } else {
        $response['message'] = "Customer not found";
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