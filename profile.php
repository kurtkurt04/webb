<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: black;
            color: gold;
            overflow-x: hidden;
        }
        .dashboard-content {
            margin-left: 270px;
            padding: 30px;
            height: 100vh;
            overflow-y: auto;
            text-align: center;
        }
        .profile-img2 {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid gold;
            object-fit: cover;
        }
        .profile-box {
            background: #c9a74a;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            margin: 20px auto;
        }
        .logout-btn {
            background: red;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-content">
        <h2>PROFILE</h2>
        <img src="image.png" alt="Profile" class="profile-img2">
        <h3>Leonard P. Pogi</h3>
        <p>Status: Manager</p>
        <div class="profile-box">
            <button class="btn w-100">Change Password</button>
            <button class="btn w-100 mt-2">Change Email</button>
            <button class="btn w-100 mt-2">Change Username</button>
        </div>
        <a href="login.php" class="logout-btn">LOGOUT</a>
    </div>
</body>
</html>