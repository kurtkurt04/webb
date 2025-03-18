<?php
session_start();
include 'db_connect.php';

$message = ""; // Initialize message
$debug = false; // Set to true to see debugging info

// Debug function
function debug_token($email, $token, $expiry, $conn) {
    echo "<div style='background: #f5f5f5; border: 1px solid #ddd; padding: 10px; margin: 10px 0; color: #333;'>";
    echo "<strong>DEBUG INFO (remove in production):</strong><br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Token: " . htmlspecialchars($token) . "<br>";
    echo "Expiry: " . htmlspecialchars($expiry) . "<br>";
    
    // Check if token is saved
    $check = $conn->prepare("SELECT reset_token, reset_expires FROM admin WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "Token in DB: " . htmlspecialchars($row['reset_token']) . "<br>";
        echo "Expiry in DB: " . htmlspecialchars($row['reset_expires']) . "<br>";
    } else {
        echo "No token found in database for this email.<br>";
    }
    
    echo "</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id, username FROM admin WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                // Generate token
                $token = bin2hex(random_bytes(16)); // Shorter token, less chance of corruption
                $expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // Longer expiry time
                
                // Store token in database - make sure these columns exist
                $update = $conn->prepare("UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?");
                if (!$update) {
                    $message = "Database error: " . $conn->error;
                } else {
                    $update->bind_param("sss", $token, $expiry, $email);
                    $result = $update->execute();
                    
                    if ($result) {
                        // Create reset link
                        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . urlencode($token);
                        
                        // In a real application, you would send this link via email
                        $message = "Password reset link has been generated. <br>Reset link: <a href='$resetLink'>$resetLink</a>";
                        
                        // Debug info
                        if ($debug) {
                            debug_token($email, $token, $expiry, $conn);
                        }
                    } else {
                        $message = "Failed to update token: " . $update->error;
                    }
                    $update->close();
                }
            } else {
                $message = "No account found with that email address";
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
    <title>CELESTIAL JEWELS - Forgot Password</title>
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
        <h4>Reset Password</h4>
        
        <?php if (!empty($message)): ?>
            <p class="message <?php echo strpos($message, "generated") !== false ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3 text-start">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <button type="submit" class="btn btn-custom">Reset Password</button>
            <p class="back-to-login"><a href="login.php" class="text-dark">Back to Login</a></p>
        </form>
    </div>
</body>
</html>