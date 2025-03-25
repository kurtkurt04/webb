<?php
// Include database connection
require_once 'functions.php';

// Function to get the last 7 days with their dates
function getLastSevenDays() {
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $days[] = [
            'date' => date('Y-m-d', strtotime("-$i days")),
            'day' => date('D', strtotime("-$i days"))
        ];
    }
    return $days;
}

// Get the last 7 days
$days = getLastSevenDays();

// Initialize data arrays
$salesData = [];
$userData = [];

// Prepare initial data structure
foreach ($days as $day) {
    $salesData[] = [
        'date' => $day['date'],
        'day' => $day['day'],
        'total_sales' => 0,
        'total_products' => 0,
        'orders_count' => 0
    ];
    
    $userData[] = [
        'date' => $day['date'],
        'day' => $day['day'],
        'new_users' => 0
    ];
}

// Fetch detailed sales data for the last 7 days
$sales_sql = "
    SELECT 
        DATE(order_date) as order_date,
        SUM(total_amount) as daily_sales,
        SUM((SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id)) as total_products,
        COUNT(*) as orders_count
    FROM 
        orders o
    WHERE 
        order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY 
        DATE(order_date)
";

$sales_result = mysqli_query($conn, $sales_sql);

if ($sales_result) {
    while ($row = mysqli_fetch_assoc($sales_result)) {
        // Find the index for this date
        $index = array_search($row['order_date'], array_column($salesData, 'date'));
        
        if ($index !== false) {
            $salesData[$index]['total_sales'] = floatval($row['daily_sales']);
            $salesData[$index]['total_products'] = intval($row['total_products']);
            $salesData[$index]['orders_count'] = intval($row['orders_count']);
        }
    }
}

// Fetch new user registrations
$users_sql = "
    SELECT 
        DATE(registration_date) as reg_date,
        COUNT(*) as new_users
    FROM 
        customers_tbl
    WHERE 
        registration_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY 
        DATE(registration_date)
";

$users_result = mysqli_query($conn, $users_sql);

if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        // Find the index for this date
        $index = array_search($row['reg_date'], array_column($userData, 'date'));
        
        if ($index !== false) {
            $userData[$index]['new_users'] = intval($row['new_users']);
        }
    }
}

// Prepare data for charts
$salesValues = array_column($salesData, 'total_sales');
$productValues = array_column($salesData, 'total_products');
$userValues = array_column($userData, 'new_users');
$dayLabels = array_column($salesData, 'day');

// Prepare recent orders query with more details
$orders_sql = "
    SELECT 
        o.order_id, 
        o.customer_id, 
        o.order_date,
        o.total_amount, 
        o.status,
        o.payment_method,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_products
    FROM 
        orders o
    ORDER BY 
        o.order_date DESC
    LIMIT 10
";

$orders_result = mysqli_query($conn, $orders_sql);

// Debug information
$total_sales = array_sum($salesValues);
$total_products = array_sum($productValues);
$total_new_users = array_sum($userValues);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="chart-title">SALES</h5>
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="chart-details">
                    <p>Total Sales (Last 7 Days): $<?php echo number_format($total_sales, 2); ?></p>
                    <p>Total Products Sold: <?php echo $total_products; ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="chart-title">NEW USERS</h5>
                    <canvas id="usersChart"></canvas>
                </div>
                <div class="chart-details">
                    <p>Total New Users (Last 7 Days): <?php echo $total_new_users; ?></p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h5 class="chart-title">RECENT ORDERS</h5>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Total Products</th>
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
                                <td>{$row['total_products']}</td>
                                <td>" . ($row['payment_method'] ? $row['payment_method'] : 'N/A') . "</td>
                                <td><span class='status-badge {$statusClass}'>{$row['status']}</span></td>
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

    <script>
    // Chart.js configuration for Sales
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dayLabels); ?>,
            datasets: [
                {
                    label: 'Total Sales ($)',
                    data: <?php echo json_encode($salesValues); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Products Sold',
                    data: <?php echo json_encode($productValues); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Chart.js configuration for Users
    var userCtx = document.getElementById('usersChart').getContext('2d');
    var usersChart = new Chart(userCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dayLabels); ?>,
            datasets: [{
                label: 'New Users',
                data: <?php echo json_encode($userValues); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>
</html>