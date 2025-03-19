<?php
session_start(); 

// Database Connection
$host = "localhost";
$username = "root";  
$password = "";      
$database = "celestial_jewels";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// -------------------------------
// AUTHENTICATION FUNCTIONS
// -------------------------------

// Login User
function loginUser($email, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            return true; 
        }
    }
    return false;
}

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Logout function
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// -------------------------------
// DASHBOARD FUNCTIONS
// -------------------------------

// Fetch Sales Data
function getSalesData() {
    global $conn;
    $sql = "SELECT day, sales FROM sales_data";
    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return json_encode($data);
}

// Fetch User Registrations
function getUserData() {
    global $conn;
    $sql = "SELECT day, users FROM user_data";
    $result = $conn->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return json_encode($data);
}

// Fetch Orders
function getOrders() {
    global $conn;
    $sql = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = $conn->query($sql);

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return json_encode($orders);
}

// Update Order Status
function updateOrderStatus($orderId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    return $stmt->execute();
}

// Count New Orders (for notifications)
function countNewOrders() {
    global $conn;
    $sql = "SELECT COUNT(*) AS count FROM orders WHERE status='Pending'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['count'];
}

// -------------------------------
// PRODUCT MANAGEMENT FUNCTIONS
// -------------------------------

// Fetch All Products
function getProducts() {
    global $conn;
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return json_encode($products);
}

// Add a Product
function addProduct($name, $price, $stock) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, price, stock) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $stock);
    return $stmt->execute();
}

// Update a Product
function updateProduct($id, $name, $price, $stock) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?");
    $stmt->bind_param("sdii", $name, $price, $stock, $id);
    return $stmt->execute();
}

// Delete a Product
function deleteProduct($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
