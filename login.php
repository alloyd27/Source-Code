<?php
session_start();
require_once 'db_connection.php'; // Make sure this file correctly connects to the database

// If user is already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errorMessage = ""; // Store error message if login fails

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameInput = trim($_POST['username']);
    $passwordInput = trim($_POST['password']);

    // Hardcoded emergency login for admin
    if ($usernameInput === 'admin' && $passwordInput === 'admin241') {
        $_SESSION['user_id'] = 'admin'; // Store admin session
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';

        session_set_cookie_params(0); // Session expires when browser closes
        header("Location: index.php");
        exit();
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $usernameInput);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $dbUsername, $storedPassword);
        $stmt->fetch();

        if (password_verify($passwordInput, $storedPassword)) {
            // Store user details in session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $dbUsername;

            session_set_cookie_params(0); // Session expires when browser closes
            header("Location: index.php");
            exit();
        } else {
            $errorMessage = "Invalid password. Please try again.";
        }
    } else {
        $errorMessage = "Username not found. Please try again.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="short icon" type="x-icon" href="img/logo.png">
    <title>Admin Login</title>
     <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #004da8, #003366);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 460px; /* Increased width */
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo {
            width: 100px; /* Adjusted logo size */
            margin-bottom: 15px;
        }

        h2 {
            font-size: 25px; /* Slightly larger for better readability */
            font-weight: 550;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 15px;
            font-weight: 300;
            margin-bottom: 40px;
        }

        input {
            width: 100%;
            padding: 14px; /* More comfortable input size */
            margin: 12px 0;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            font-size: 16px; /* Larger input text */
            background: rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
            transition: border-color 0.3s;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        input:focus {
            border-color: #66b3ff;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background-color: #FFD700;
            color: #003366;
            font-size: 18px; /* Larger button text */
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-btn:hover {
            background-color: #FFC107;
        }

        .forgot-password {
            display: block;
            margin-top: 12px;
            color: #FFD700;
            font-size: 15px;
            text-decoration: none;
            
            margin-bottom: 15px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .notification {
            margin-top: 15px;
            padding: 12px;
            font-size: 15px;
            border-radius: 8px;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .notification.error {
            background-color: #e74c3c;
            color: white;
        }

        .footer {
            font-size: 14px;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Animation */
        .login-container {
            animation: fadeIn 0.7s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="logo.png" alt="Lucban Logo" class="logo">
        <h2>Enhanced Monitoring System</h2>
        <h3>Municipality of Lucban, Quezon</h3>
        
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>

       <?php if (!empty($errorMessage)): ?>
    <div class="notification error"><?php echo $errorMessage; ?></div>
<?php endif; ?>


        <br>
        <br>
        <div class="footer">© 2025 Municipality of Lucban, Quezon</div>
    </div>

</body>
</html>
