<?php
session_start();

// Redirect if session data is missing
if (!isset($_SESSION['email'], $_SESSION['otp'], $_SESSION['otp_time'])) {
    header("Location: forgot_password.php");
    exit();
}

// Check if OTP expired (10 minutes)
if (time() - $_SESSION['otp_time'] > 600) {
    $_SESSION['error_message'] = "Your session has expired. Please request a new OTP.";
    header("Location: forgot_password.php");
    exit();
}

// Database connection
$host = "localhost";
$dbname = "celestial_jewels";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'], $_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['email'];

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Both password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        try {
            // Check if the email exists
            $check_stmt = $conn->prepare("SELECT * FROM admin WHERE email = :email");
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                // Remove password hashing for testing
                $hashed_password = $new_password; // Store as plain text

                // Update the password
                $update_stmt = $conn->prepare("UPDATE admin SET password = :password WHERE email = :email");
                $update_stmt->bindParam(':password', $hashed_password);
                $update_stmt->bindParam(':email', $email);
                $update_stmt->execute();

                // Check if update was successful
                if ($update_stmt->rowCount() > 0) {
                    // Clear session
                    session_unset();
                    session_destroy();
                    
                    // Redirect to login with success message
                    header("Location: login.php?success=Password updated successfully.");
                    exit();
                } else {
                    $error_message = "Password update failed. Try a different password.";
                }
            } else {
                $error_message = "Email not found in the database.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password | Celestial Jewels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 450px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.1s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-back {
            background-color: #6c757d;
            margin-right: 10px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fde8e6;
            border-radius: 4px;
            border-left: 4px solid #e74c3c;
        }
        
        .success {
            color: #2ecc71;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
            border-left: 4px solid #2ecc71;
        }
        
        p {
            margin-bottom: 15px;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .password-requirements {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }
            
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Celestial Jewels</div>
        <h2>Set New Password</h2>
        
        <?php if(isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <p>Please create a new password for your account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
                <div class="password-requirements">Must be at least 8 characters long</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            
            <div class="button-group">
                <a href="forgot_password.php" class="btn btn-back">Back</a>
                <button type="submit" class="btn">Update Password</button>
            </div>
        </form>
    </div>
</body>
</html>
