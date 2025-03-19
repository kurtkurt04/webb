<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT name, username, email, password FROM admin WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    
    // Password change logic without debug info
    $password_verified = true;
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Current password is required to set a new password";
            $password_verified = false;
        } else {
            // Simple direct comparison without hashing
            if ($current_password === $user['password']) {
                // Password matches
            } else {
                $error = "Current password is incorrect";
                $password_verified = false;
            }
        }
    }

    // Continue if password verification passed
    if ($password_verified) {
        // Check for existing username/email
        $check_stmt = $conn->prepare("SELECT id FROM admin WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $username, $email, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            // Build update query
            $query = "UPDATE admin SET name = ?, username = ?, email = ?";
            $params = [$name, $username, $email];
            $types = "sss";
            
            // Add password update if provided and verified
            if (!empty($new_password)) {
                $query .= ", password = ?";
                $params[] = $new_password; // Store as plain text for testing
                $types .= "s";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";
            
            $update_stmt = $conn->prepare($query);
            $update_stmt->bind_param($types, ...$params);
            
            if ($update_stmt->execute()) {
                // Check if rows were affected
                if ($update_stmt->affected_rows > 0) {
                    $success = "Profile updated successfully!";
                    
                    // Update session variables
                    $_SESSION['username'] = $username;
                    
                    // If password was changed
                    if (!empty($new_password)) {
                        $success .= " Password has been changed.";
                    }
                    
                    // Refresh page to show updated data
                    header("Refresh: 2");
                } else {
                    $success = "No changes detected or data is the same as before.";
                }
            } else {
                $error = "Error updating profile: " . $update_stmt->error;
            }
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
        <h2 class="mb-4">Edit Profile</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control"
                       value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Current Password (required to change password)</label>
                <input type="password" name="current_password" class="form-control">
            </div>
            
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>