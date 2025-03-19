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

// Get search term from request
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

// Search customers
$customers = array();

try {
    // If search term is empty, return all customers
    if (empty($searchTerm)) {
        $sql = "SELECT customer_id, username, email, phone_number FROM customers_tbl";
        $result = $conn->query($sql);
    } else {
        // Search with LIKE operator
        $sql = "SELECT customer_id, username, email, phone_number FROM customers_tbl 
                WHERE username LIKE ? OR email LIKE ? OR CAST(phone_number AS CHAR) LIKE ?";
        $stmt = $conn->prepare($sql);
        
        $searchParam = "%" . $searchTerm . "%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
    
    // Fetch results
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
} catch (Exception $e) {
    // Log error (in a production environment)
    error_log("Search error: " . $e->getMessage());
}

$conn->close();

// Return as JSON
header('Content-Type: application/json');
echo json_encode($customers);
?>