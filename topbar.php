<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: relative;
            padding: 15px 30px;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
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
            background: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
        }
        .notification-panel {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            color: black;
            padding: 15px;
            border-radius: 5px;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }
        .notification-panel ul {
            list-style: none;
            padding: 0;
        }
        .notification-panel li {
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .notification-panel li:last-child {     
            border-bottom: none;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
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
            <p><strong>Notifications</strong></p>
            <ul id="notificationList"></ul>
        </div>
    </div>

    <script>
        function toggleNotifications() {
            var panel = document.getElementById('notificationPanel');
            panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
        }

        function addNotification(message) {
            var notificationList = document.getElementById('notificationList');
            var notificationItem = document.createElement('li');
            notificationItem.textContent = message;
            notificationList.appendChild(notificationItem);

            var count = document.getElementById('notificationCount');
            count.textContent = parseInt(count.textContent) + 1;
        }

        // Sample Notifications
        document.addEventListener("DOMContentLoaded", function () {
            addNotification("New order received.");
            addNotification("Stock update required.");
            addNotification("System maintenance scheduled.");
        });
    </script>
</body>
</html>