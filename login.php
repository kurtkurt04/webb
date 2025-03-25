<?php
session_start();
include 'db_connect.php';

$error = ""; // Initialize error message

// Define the loginUser function
function loginUser($username, $password) {
    global $conn;
    
    // Check if connection is valid
    if (!$conn) {
        error_log("Database connection failed");
        return false;
    }
    
    try {
        // Prepare statement
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        
        // Check if prepare was successful
        if ($stmt === false) {
            error_log("Prepare statement failed: " . $conn->error);
            return false;
        }
        
        // Bind parameters
        if (!$stmt->bind_param("s", $username)) {
            error_log("Binding parameters failed: " . $stmt->error);
            return false;
        }
        
        // Execute query
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();
            
            if ($password === $db_password) { // Direct password comparison
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_username;
                return true;
            }
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Exception in loginUser: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); 
    $password = trim($_POST['password']);
    
    if (loginUser($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CELESTIAL JEWELS Login</title>
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
        .forgot-password {
            margin-top: 10px;
        }
        .error-message {
            color: darkred;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>CELESTIAL JEWELS</h2>
        
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3 text-start">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-custom">Log In</button>
            <p class="forgot-password"><a href="forgot_password.php" class="text-dark">Forgot password?</a></p>
        </form>
    </div>
</body>
</html>
