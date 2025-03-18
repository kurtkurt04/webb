<?php
session_start();
include 'db_connect.php';

$message = "";
$validToken = false;
$token = "";
$debug = false; // Set to true to see debugging info

// Debug function
function debug_token_reset($token, $conn) {
    echo "<div style='background: #f5f5f5; border: 1px solid #ddd; padding: 10px; margin: 10px 0; color: #333;'>";
    echo "<strong>DEBUG INFO (remove in production):</strong><br>";
    echo "Token from URL: " . htmlspecialchars($token) . "<br>";
    
    // Check if token exists
    $check = $conn->prepare("SELECT id, username, email, reset_expires FROM admin WHERE reset_token = ?");
    $check->bind_param("s", $token);
    $check->execute();
    $result = $check->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "Found user: " . htmlspecialchars($row['username']) . "<br>";
        echo "User ID: " . htmlspecialchars($row['id']) . "<br>";
        echo "Email: " . htmlspecialchars($row['email']) . "<br>";
        echo "Expiry: " . htmlspecialchars($row['reset_expires']) . "<br>";
        echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
        
        if (strtotime($row['reset_expires']) > time()) {
            echo "Token is still valid (not expired)<br>";
        } else {
            echo "Token has expired<br>";
        }
    } else {
        echo "No user found with this token.<br>";
        
        // Show first few users for debugging
        $users = $conn->query("SELECT id, username, email, reset_token FROM admin LIMIT 5");
        echo "<br>First few users in database:<br>";
        while ($user = $users->fetch_assoc()) {
            echo "ID: " . $user['id'] . ", Username: " . $user['username'] . 
                 ", Email: " . $user['email'] . ", Token: " . $user['reset_token'] . "<br>";
        }
    }
    
    echo "</div>";
}

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    if ($debug) {
        debug_token_reset($token, $conn);
    }
    
    // Verify token is valid and not expired
    $stmt = $conn->prepare("SELECT id FROM admin WHERE reset_token = ? AND reset_expires > NOW()");
    
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $validToken = true;
        } else {
            $message = "Invalid or expired reset link. Please request a new password reset.";
        }
        $stmt->close();
    } else {
        $message = "Database error: " . $conn->error;
    }
} else {
    $message = "Missing token. Invalid reset link.";
}

// Process new password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $token = isset($_GET['token']) ? trim($_GET['token']) : '';
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Re-validate token
    $check = $conn->prepare("SELECT id FROM admin WHERE reset_token = ? AND reset_expires > NOW()");
    $check->bind_param("s", $token);
    $check->execute();
    $check->store_result();
    $tokenValid = ($check->num_rows > 0);
    $check->close();
    
    if (!$tokenValid) {
        $message = "Invalid or expired reset link. Please request a new password reset.";
    }
    // Validate passwords match
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match";
        $validToken = true; // Keep form visible
    } 
    // Validate password length
    elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long";
        $validToken = true; // Keep form visible
    } 
    else {
        // Update password in database
        $stmt = $conn->prepare("UPDATE admin SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        
        if ($stmt) {
            // Note: In a real application, you should hash the password
            // For this example we're keeping the direct password storage to match your existing login system
            $stmt->bind_param("ss", $password, $token);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $message = "Your password has been updated successfully";
                $validToken = false; // Hide the form
                header("refresh:3;url=index.php"); // Redirect after 3 seconds
            } else {
                $message = "Failed to update password. Please try again.";
                $validToken = true; // Keep form visible
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CELESTIAL JEWELS - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #111, #333);
            color: gold;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 350px;
            padding: 30px;
            background: #c9a74a;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .login-container h2 {
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-control {
            border: none;
            background: #f5deb3;
            color: black;
        }
        .btn-custom {
            background: black;
            color: gold;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        .btn-custom:hover {
            background: darkgoldenrod;
            color: black;
        }
        .back-to-login {
            margin-top: 10px;
        }
        .message {
            margin-top: 10px;
            font-weight: bold;
        }
        .success-message {
            color: darkgreen;
        }
        .error-message {
            color: darkred;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>CELESTIAL JEWELS</h2>
        <h4>Create New Password</h4>
        
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, "successfully") !== false ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        
        <?php if ($validToken): ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?token=" . htmlspecialchars($token); ?>">
                <div class="mb-3 text-start">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-custom">Update Password</button>
            </form>
        <?php else: ?>
            <p class="back-to-login"><a href="login.php" class="text-dark">Back to Login</a></p>
        <?php endif; ?>
    </div>
    
    <script>
        // Client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.querySelector('input[name="password"]').value;
                    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert("Passwords do not match!");
                    }
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        alert("Password must be at least 6 characters long");
                    }
                });
            }
        });
    </script>
</body>
</html>