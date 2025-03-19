<?php
// Start session to store OTP
session_start();

// Import PHPMailer classes
require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to generate OTP
function generateOTP($length = 6) {
    return str_pad(random_int(100000, 999999), $length, '0', STR_PAD_LEFT);
}

// Function to send OTP via email
function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'killernasus04@gmail.com'; // Your Gmail
        $mail->Password   = 'hssy yghe dgdz gfcs';    // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('killernasus04@gmail.com', 'Celestial Jewels');
        $mail->addAddress($email);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP for verification is: <b>$otp</b><br>This OTP expires in 10 minutes.";
        $mail->AltBody = "Your OTP for verification is: $otp\nThis OTP expires in 10 minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Process email input
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $otp = generateOTP();
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();
    $_SESSION['email'] = $email;

    if (sendOTPEmail($email, $otp)) {
        $success_message = "OTP sent successfully to $email.";
    } else {
        $error_message = "Failed to send OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | Celestial Jewels</title>
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
        
        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="text"]:focus {
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
        <h2>Forgot Password</h2>
        
        <?php if(isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success_message)): ?>
            <div class="success"><?php echo $success_message; ?></div>
            <p>Please enter the OTP sent to your email:</p>
            <form action="change_password.php" method="post">
                <div class="form-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" name="otp" id="otp" required>
                </div>
                <div class="button-group">
                    <a href="login.php" class="btn btn-back">Back to Login</a>
                    <button type="submit" class="btn">Verify OTP</button>
                </div>
            </form>
        <?php else: ?>
            <p>Enter your email address to receive a password reset OTP.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" required placeholder="your@email.com">
                </div>
                <div class="button-group">
                    <a href="login.php" class="btn btn-back">Back to Login</a>
                    <button type="submit" class="btn">Send OTP</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>