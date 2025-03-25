<?php
// notifications.php - Backend API to fetch notifications
// This file should only handle the API request and return JSON

// Only process if this file is being accessed directly as an API endpoint
// or if it's a direct AJAX request
if(basename($_SERVER['PHP_SELF']) == 'notifications.php' && empty($_SERVER['HTTP_X_REQUESTED_WITH']) && !isset($_GET['include'])) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "celestial_jewels";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        header('Content-Type: application/json');
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
        header('Content-Type: application/json');
        echo json_encode($notifications);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

    $conn->close();
    exit; // Stop execution to prevent HTML output
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Celestial Jewels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: relative;
            padding: 15px 30px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .notification-icon {
            font-size: 24px;
            cursor: pointer;
            position: relative;
            margin-right: 15px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .notification-panel {
            display: none;
            position: absolute;
            right: 0;
            top: 60px;
            background: white;
            color: black;
            padding: 15px;
            border-radius: 5px;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }
        .notification-panel ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notification-panel li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .notification-panel li:hover {
            background-color: #f8f9fa;
        }
        .notification-panel li:last-child {     
            border-bottom: none;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .notification-time {
            font-size: 0.8em;
            color: #6c757d;
            display: block;
            margin-top: 5px;
        }
        .notification-order {
            border-left: 4px solid #28a745;
            padding-left: 10px;
        }
        .notification-product {
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .notification-customer {
            border-left: 4px solid #fd7e14;
            padding-left: 10px;
        }
        .notification-stock {
            border-left: 4px solid #dc3545;
            padding-left: 10px;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .mark-all-read {
            font-size: 12px;
            cursor: pointer;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-right">
            <div class="notification-icon" onclick="toggleNotifications()">🔔
                <span class="notification-badge" id="notificationCount">0</span>
            </div>
            <a href="profile.php">
                <img src="image.png" alt="Profile" class="profile-img">
            </a>
        </div>
        <div class="notification-panel" id="notificationPanel">
            <div class="notification-header">
                <p class="mb-0"><strong>Notifications</strong></p>
                <span class="mark-all-read" onclick="markAllAsRead()">Mark all as read</span>
            </div>
            <ul id="notificationList">
                <li>Loading notifications...</li>
            </ul>
        </div>
    </div>

    <script>
        // Store read notifications in localStorage
        let readNotifications = JSON.parse(localStorage.getItem('readNotifications')) || [];
        let notifications = [];
        
        function toggleNotifications() {
            var panel = document.getElementById('notificationPanel');
            panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
        }

        function formatTimestamp(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleString();
        }

        function addNotification(notification) {
            var notificationList = document.getElementById('notificationList');
            var notificationItem = document.createElement('li');
            
            // Add class based on notification type
            notificationItem.classList.add('notification-' + notification.type);
            
            // Check if this notification has been read
            if (readNotifications.includes(notification.type + '-' + notification.id)) {
                notificationItem.style.opacity = '0.6';
            } else {
                // Increment count only for unread notifications
                incrementNotificationCount();
            }
            
            notificationItem.innerHTML = `
                ${notification.message}
                <span class="notification-time">${formatTimestamp(notification.timestamp)}</span>
            `;
            
            // Add click event to mark as read
            notificationItem.addEventListener('click', function() {
                markAsRead(notification.type + '-' + notification.id);
                notificationItem.style.opacity = '0.6';
                
                // Navigate based on notification type
                switch(notification.type) {
                    case 'order':
                        window.location.href = 'order-details.php?id=' + notification.id;
                        break;
                    case 'product':
                        window.location.href = 'product-details.php?id=' + notification.id;
                        break;
                    case 'customer':
                        window.location.href = 'customer-details.php?id=' + notification.id;
                        break;
                    case 'stock':
                        window.location.href = 'inventory.php?low=true';
                        break;
                }
            });
            
            notificationList.appendChild(notificationItem);
        }

        function incrementNotificationCount() {
            var count = document.getElementById('notificationCount');
            count.textContent = parseInt(count.textContent) + 1;
        }

        function markAsRead(notificationId) {
            if (!readNotifications.includes(notificationId)) {
                readNotifications.push(notificationId);
                localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                
                // Decrement count
                var count = document.getElementById('notificationCount');
                const currentCount = parseInt(count.textContent);
                if (currentCount > 0) {
                    count.textContent = currentCount - 1;
                }
            }
        }

        function markAllAsRead() {
            const notificationItems = document.querySelectorAll('#notificationList li');
            notificationItems.forEach(item => {
                item.style.opacity = '0.6';
            });
            
            // Reset count
            document.getElementById('notificationCount').textContent = '0';
            
            // Store all as read
            const allNotifications = notifications.map(n => n.type + '-' + n.id);
            readNotifications = [...new Set([...readNotifications, ...allNotifications])];
            localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
        }

        // Fetch notifications from server
        function fetchNotifications() {
            // Use a timestamp parameter to prevent caching
            fetch('get_notifications.php?t=' + new Date().getTime())
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear existing notifications
                    document.getElementById('notificationList').innerHTML = '';
                    document.getElementById('notificationCount').textContent = '0';
                    
                    // Check if there's an error message
                    if (data.error) {
                        document.getElementById('notificationList').innerHTML = '<li>Error: ' + data.error + '</li>';
                        return;
                    }
                    
                    // Store fetched notifications
                    notifications = data;
                    
                    // Add each notification to the panel
                    data.forEach(notification => {
                        addNotification(notification);
                    });
                    
                    // If no notifications
                    if (data.length === 0) {
                        document.getElementById('notificationList').innerHTML = '<li>No new notifications</li>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                    document.getElementById('notificationList').innerHTML = '<li>Error loading notifications. Please try again.</li>';
                });
        }

        // Initial fetch on page load
        document.addEventListener("DOMContentLoaded", function() {
            fetchNotifications();
            
            // Set up periodic polling (every 30 seconds)
            setInterval(fetchNotifications, 30000);
        });
    </script>
</body>
</html>