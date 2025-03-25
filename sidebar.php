<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
    background: black;
    color: gold;
}
        .dashboard-sidebar {
            background: #c9a74a;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
        }
        .dashboard-sidebar h4 {
            font-weight: bold;
            text-align: center;
        }
        .dashboard-sidebar .btn {
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <h4>CELESTIAL JEWELRY</h4>
        <a href="dashboard.php" class="btn btn-outline-dark">DASHBOARD</a>
        <a href="orders.php" class="btn btn-outline-dark">ORDERS</a>
        <a href="products.php" class="btn btn-outline-dark">PRODUCTS</a>
        <a href="customers.php" class="btn btn-outline-dark">CUSTOMERS</a>
    </div>
</body>
</html>