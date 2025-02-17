<?php
session_start();

// Database connection
require_once 'db_connection.php';

$token = $_GET['token'] ?? '';
$errorMessage = '';
$successMessage = '';

if (!$token) {
    $errorMessage = 'Invalid or expired token.';
} else {
    // Verify token from the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Token is valid, allow password reset
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = $_POST['password'];
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password in the database and remove reset token
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashedPassword, $token);
            $stmt->execute();

            $successMessage = "Password reset successfully. You can now <a href='login.php'>log in</a>.";
        }
    } else {
        $errorMessage = 'Invalid or expired token.';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
   <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #004da8, #003366);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .reset-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 460px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        h2 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        input {
            width: 93%;
            padding: 14px;
            margin: 12px 0;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        input:focus {
            border-color: #FFD700;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #FFD700;
            color: #003366;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #FFC107;
        }

        .message {
            margin-top: 15px;
            padding: 12px;
            font-size: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .error-message {
            background-color: #e74c3c;
            color: white;
        }

        .success-message {
            background-color: #2ecc71;
            color: white;
        }

        .back-link {
            display: block;
            margin-top: 12px;
            font-size: 15px;
            color: #FFD700;
            text-decoration: none;
            
            margin-bottom: 30px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Enter your new password" required>
            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
        
        <a href="login.php" class="back-link">Go back to login page</a>

        <?php if ($errorMessage): ?>
            <div class="message error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="message success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
