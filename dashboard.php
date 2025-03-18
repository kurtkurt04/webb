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

$result = mysqli_query($conn, $sql);

// Initialize sales data array
$salesData = [];
$userData = [];

// Set up default data
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
foreach ($days as $day) {
    $salesData[] = ['day' => $day, 'sales' => 0];
    $userData[] = ['day' => $day, 'users' => 0];
}

// Process orders to get sales data
if ($result && mysqli_num_rows($result) > 0) {
    $dayMap = [
        0 => 6, // Sunday -> index 6
        1 => 0, // Monday -> index 0
        2 => 1, // Tuesday -> index 1
        3 => 2, // Wednesday -> index 2
        4 => 3, // Thursday -> index 3
        5 => 4, // Friday -> index 4
        6 => 5, // Saturday -> index 5
    ];
    
    // Clone the result for table display
    $orders_result = $result;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $date = strtotime($row['order_date']);
        $dayOfWeek = date('w', $date);
        $index = $dayMap[$dayOfWeek];
        
        // Add sales amount to the corresponding day
        $salesData[$index]['sales'] += $row['total_amount'];
        
        // Count unique customers (simple approach)
        $userData[$index]['users'] += 1;
    }
    
    // Reset the result pointer for the table
    mysqli_data_seek($orders_result, 0);
} else {
    $orders_result = $result;
}

// Extract just the sales values for the JavaScript
$salesValues = array_map(function($item) { return $item['sales']; }, $salesData);
$userValues = array_map(function($item) { return $item['users']; }, $userData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            overflow-y: auto; /* Add scrolling for content */
            max-height: 100vh;
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
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 3px solid gold; /* Adding gold border to match theme */
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5); /* Adding a gold glow effect */
        }
        
        .chart-title {
            color: #333;
            font-weight: bold;
            border-bottom: 2px solid gold;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 3px solid gold;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
            overflow-x: auto;
        }
        
        .table {
            color: #333;
        }
        
        .table thead th {
            background-color: gold;
            color: black;
            border-color: #e0e0e0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            min-width: 100px;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: black;
        }
        
        .status-processing {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-shipped {
            background-color: #007bff;
            color: white;
        }
        
        .status-delivered {
            background-color: #28a745;
            color: white;
        }
        
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">DASHBOARD</h2>
            <?php include 'topbar.php'; ?>
        </div>

        <div class="btn-group mb-3">
            <button class="btn btn-dark active" onclick="updateChart('day', event)">DAY</button>
            <button class="btn btn-warning" onclick="updateChart('week', event)">WEEK</button>
            <button class="btn btn-warning" onclick="updateChart('month', event)">MONTH</button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="chart-title">SALES</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="chart-title">USERS</h5>
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Updated Orders Table -->
        <div class="table-container">
            <h5 class="chart-title">RECENT ORDERS</h5>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($orders_result && mysqli_num_rows($orders_result) > 0) {
                        while ($row = mysqli_fetch_assoc($orders_result)) {
                            // Determine status badge class
                            $statusClass = 'status-pending';
                            switch(strtolower($row['status'])) {
                                case 'processing':
                                    $statusClass = 'status-processing';
                                    break;
                                case 'shipped':
                                    $statusClass = 'status-shipped';
                                    break;
                                case 'delivered':
                                    $statusClass = 'status-delivered';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-cancelled';
                                    break;
                            }
                            
                            echo "<tr>
                                <td>#{$row['order_id']}</td>
                                <td><b>{$row['customer_id']}</b></td>
                                <td>" . date('M d, Y', strtotime($row['order_date'])) . "</td>
                                <td>\${$row['total_amount']}</td>
                                <td>" . ($row['payment_method'] ? $row['payment_method'] : 'N/A') . "</td>
                                <td><span class='status-badge {$statusClass}'>{$row['status']}</span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let salesChart, usersChart;
        
        // Sample data for week and month views
        const weekSalesData = [
            <?php echo json_encode($salesValues); ?>,
            [Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), 
             Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000),
             Math.floor(Math.random() * 1000)],
            [Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), 
             Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000), Math.floor(Math.random() * 1000),
             Math.floor(Math.random() * 1000)]
        ];
        
        const weekUsersData = [
            <?php echo json_encode($userValues); ?>,
            [Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), 
             Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), Math.floor(Math.random() * 100),
             Math.floor(Math.random() * 100)],
            [Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), 
             Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), Math.floor(Math.random() * 100),
             Math.floor(Math.random() * 100)]
        ];

        function createChart(ctx, label, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: 'blue',
                        borderWidth: 1
                    }]
                }
            });
        }

        function updateChart(period, event) {
            let index = 0;
            
            switch(period) {
                case 'day':
                    index = 0;
                    break;
                case 'week':
                    index = 1;
                    break;
                case 'month':
                    index = 2;
                    break;
            }
            
            salesChart.data.datasets[0].data = weekSalesData[index];
            usersChart.data.datasets[0].data = weekUsersData[index];
            salesChart.update();
            usersChart.update();

            // Update active button state
            const buttons = document.querySelectorAll('.btn-group .btn');
            buttons.forEach(btn => btn.classList.remove('active', 'btn-dark'));
            buttons.forEach(btn => btn.classList.add('btn-warning'));
            event.target.classList.remove('btn-warning');
            event.target.classList.add('active', 'btn-dark');
        }

        window.onload = function() {
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const usersCtx = document.getElementById('usersChart').getContext('2d');

            salesChart = createChart(salesCtx, 'Sales', <?php echo json_encode($salesValues); ?>);
            usersChart = createChart(usersCtx, 'Users', <?php echo json_encode($userValues); ?>);

            // Fetch latest notifications
            fetch("data.php?type=notifications")
                .then(response => response.json())
                .then(data => {
                    // The notification handling is now in topbar.php
                })
                .catch(error => console.error("Error fetching notifications:", error));
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>