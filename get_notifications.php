<?php
// get_notifications.php - Backend API to fetch notifications

// Set headers
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "celestial_jewels";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Initialize notifications array
$notifications = [];

try {
    // Check for new orders (last 24 hours)
    $orderQuery = "SELECT order_id, customer_id, order_date, total_amount 
                FROM orders 
                WHERE order_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY order_date DESC";

    $result = $conn->query($orderQuery);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'type' => 'order',
                'message' => "New order #" . $row['order_id'] . " received for $" . $row['total_amount'],
                'timestamp' => $row['order_date'],
                'id' => $row['order_id']
            ];
        }
    }

    // Check for new products (latest 10 products)
    $productQuery = "SELECT id, name, selling_price
                    FROM products
                    ORDER BY id DESC
                    LIMIT 10";

    $result = $conn->query($productQuery);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'type' => 'product',
                'message' => "Product available: " . $row['name'] . " ($" . $row['selling_price'] . ")",
                'timestamp' => date('Y-m-d H:i:s'), // Current time
                'id' => $row['id']
            ];
        }
    }

    // Check for new customers (latest 10 customers)
    $customerQuery = "SELECT customer_id, username, email
                    FROM customers_tbl
                    ORDER BY customer_id DESC
                    LIMIT 10";

    $result = $conn->query($customerQuery);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'type' => 'customer',
                'message' => "Customer registered: " . $row['username'],
                'timestamp' => date('Y-m-d H:i:s'), // Current time
                'id' => $row['customer_id']
            ];
        }
    }

    // Check for low stock products (below 10 items)
    $lowStockQuery = "SELECT id, name, stocks
                    FROM products
                    WHERE stocks < 10
                    ORDER BY stocks ASC";

    $result = $conn->query($lowStockQuery);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'type' => 'stock',
                'message' => "Low stock alert: " . $row['name'] . " (Only " . $row['stocks'] . " left)",
                'timestamp' => date('Y-m-d H:i:s'),
                'id' => $row['id']
            ];
        }
    }

    // Sort notifications by timestamp (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Return JSON response
    echo json_encode($notifications);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>