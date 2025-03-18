<?php
// Include database connection
require_once 'functions.php';

// Fetch orders
$sql = "SELECT 
    order_id, 
    customer_id, 
    order_date,
    total_amount, 
    status,
    payment_method
FROM orders
ORDER BY order_date DESC";

$result = $conn->query($sql);

// Handle order status update if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    $updateSql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $newStatus, $orderId);
    
    if ($stmt->execute()) {
        $statusUpdateMessage = "Order #$orderId status updated to $newStatus";
        // Refresh the page to show updated data
        header("Location: orders.php?message=" . urlencode($statusUpdateMessage));
        exit();
    } else {
        $statusUpdateError = "Error updating status: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: black;
            color: gold;
            display: flex;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        
        .sidebar {
            flex: 0 0 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px; /* Match sidebar width */
            padding: 20px;
            width: calc(100% - 250px);
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
            padding: 10px 0;
        }
        
        .page-title {
            margin: 0;
            color: gold;
        }
        
        .table-container {
            max-height: calc(100vh - 130px); /* Adjusted to account for top bar */
            overflow-y: auto;
            background: #222;
            padding: 15px;
            border-radius: 10px;
        }
        
        table {
            color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        thead {
            background: gold;
            color: black;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        th, td {
            padding: 12px;
        }
        
        .btn-update {
            background: gold;
            color: black;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Order Management</h2>
            <?php include 'topbar.php'; ?>
        </div>
        
        <?php
        if (isset($_GET['message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
        }
        if (isset($statusUpdateError)) {
            echo '<div class="alert alert-danger">' . $statusUpdateError . '</div>';
        }
        ?>
        
        <div class="table-container">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>#{$row['order_id']}</td>
                                <td><b>{$row['customer_id']}</b></td>
                                <td>" . date('M d, Y', strtotime($row['order_date'])) . "</td>
                                <td>\${$row['total_amount']}</td>
                                <td>" . ($row['payment_method'] ? $row['payment_method'] : 'N/A') . "</td>
                                <td>
                                    <form method='post' action='orders.php'>
                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                        <select name='status' class='form-select'>
                                            <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='Processing' " . ($row['status'] == 'Processing' ? 'selected' : '') . ">Processing</option>
                                            <option value='Shipped' " . ($row['status'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                            <option value='Delivered' " . ($row['status'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                                            <option value='Cancelled' " . ($row['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                                        </select>
                                </td>
                                <td>
                                        <button type='submit' name='update_status' class='btn-update'>Update</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
<?php $conn->close(); ?>