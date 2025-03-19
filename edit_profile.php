<?php
session_start();

// Connect to database
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$database = "celestial_jewels";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";
$action = isset($_GET['action']) ? $_GET['action'] : ""; // Get the action from the URL

// Fetch current user data
$stmt = $conn->prepare("SELECT name, username, email, password FROM admin WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($action == "password") {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        
        if ($current_password === $user['password']) { // Simple password check for testing
            $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_password, $user_id); // Plain text for testing
            $update_stmt->execute();
            $success = "Password updated successfully!";
            $update_stmt->close();
        } else {
            $error = "Current password is incorrect!";
        }
    }

    if ($action == "email") {
        $new_email = trim($_POST['new_email']);
        
        $check_stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $new_email, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $update_stmt = $conn->prepare("UPDATE admin SET email = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_email, $user_id);
            $update_stmt->execute();
            $success = "Email updated successfully!";
            $update_stmt->close();
        }
        $check_stmt->close();
    }

    if ($action == "username") {
        $new_username = trim($_POST['new_username']);
        
        $check_stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
        $check_stmt->bind_param("si", $new_username, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $update_stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_username, $user_id);
            $update_stmt->execute();
            $success = "Username updated successfully!";
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f0f0; padding: 20px; }
        .profile-container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2 class="mb-4">
            <?php
            if ($action == "password") echo "Change Password";
            elseif ($action == "email") echo "Change Email";
            elseif ($action == "username") echo "Change Username";
            else echo "Edit Profile";
            ?>
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <?php if ($action == "password"): ?>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
            <?php elseif ($action == "email"): ?>
                <div class="form-group">
                    <label>New Email</label>
                    <input type="email" name="new_email" class="form-control" required>
                </div>
            <?php elseif ($action == "username"): ?>
                <div class="form-group">
                    <label>New Username</label>
                    <input type="text" name="new_username" class="form-control" required>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>
