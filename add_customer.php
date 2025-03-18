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

// Get form data
$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : null;
$username = $_POST['username'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Generate a default password (you should implement proper password handling)
$defaultPassword = password_hash('defaultPassword123', PASSWORD_DEFAULT);

if ($customerId) {
    // Update existing customer
    $stmt = $conn->prepare("UPDATE customers_tbl SET username=?, email=?, phone_number=? WHERE customer_id=?");
    $stmt->bind_param("ssii", $username, $email, $phone, $customerId);
} else {
    // Add new customer
    $stmt = $conn->prepare("INSERT INTO customers_tbl (username, password, email, phone_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $defaultPassword, $email, $phone);
}

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = $customerId ? "Customer updated successfully" : "Customer added successfully";
} else {
    $response['message'] = "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Return as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>