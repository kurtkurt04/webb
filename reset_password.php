<?php
// Start session to retrieve OTP
session_start();

// Database connection
$host = "localhost";
$dbname = "celestial_jewels";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if OTP is valid
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // OTP verification
    if (isset($_POST['otp']) && !empty($_POST['otp'])) {
        $entered_otp = $_POST['otp'];
        
        // Check if reset session exists
        if (isset($_SESSION['reset_otp']) && isset($_SESSION['reset_email']) && isset($_SESSION['reset_time'])) {
            $stored_otp = $_SESSION['reset_otp'];
            $email = $_SESSION['reset_email'];
            $time = $_SESSION['reset_time'];
            
            // Check if OTP has expired (10 minutes)
            if (time() - $time > 600) {
                $error_message = "OTP has expired. Please request a new one.";
            } else if ($entered_otp == $stored_otp) {
                // OTP is correct, show password reset form
                $otp_verified = true;
            } else {
                $error_message = "Invalid OTP. Please try again.";
            }
        } else {
            $error_message = "Session expired. Please request a new OTP.";
        }
    }
    
    // Password reset form submission
    if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password != $confirm_password) {
            $error_message = "Passwords do not match.";
        } else if (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } else {
            $email = $_SESSION['reset_email'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                // Clear session
                unset($_SESSION['reset_otp']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_time']);
                
                $success_message = "Password has been reset successfully.";
                $password_reset = true;
            } else {
                $error_message = "Failed to reset password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f7f7f7;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        
        <?php if(isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success_message)): ?>
            <div class="success"><?php echo $success_message; ?></div>
            <?php if(isset($password_reset) && $password_reset): ?>
                <p>You can now <a href="login.php">login</a> with your new password.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if(!isset($otp_verified) && !isset($password_reset)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" name="otp" id="otp" required>
                </div>
                <button type="submit">Verify OTP</button>
            </form>
        <?php elseif(isset($otp_verified) && $otp_verified && !isset($password_reset)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>