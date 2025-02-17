<?php
session_start();

require_once 'db_connection.php';

$errorEmail = false;
$successMessage = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailInput = trim($_POST['email']);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $emailInput);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $username);
        $stmt->fetch();

        // Generate a reset token and store it in the database
        $resetToken = bin2hex(random_bytes(50)); // 100 character long random token
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
        $stmt->bind_param("si", $resetToken, $userId);
        $stmt->execute();

        // Include PHPMailer classes
require '/home/u766310616/domains/admin-accesscontrol.com/public_html/phpmailer/src/Exception.php';
require '/home/u766310616/domains/admin-accesscontrol.com/public_html/phpmailer/src/PHPMailer.php';
require '/home/u766310616/domains/admin-accesscontrol.com/public_html/phpmailer/src/SMTP.php';
        // Send the reset email using PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'alloydpelaez@gmail.com'; 
        $mail->Password = 'utxafvgsiwvtixod';   // Your Gmail password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Admin');
        $mail->addAddress($emailInput);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click the following link to reset your password: <a href='http://admin-accesscontrol.com/reset_password.php?token=$resetToken'>Reset Password</a>";

        // Send email
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $successMessage = "A password reset link has been sent to your email address.";
        }
    } else {
        $errorEmail = true; // Email not found
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
    <title>Forgot Password</title>
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

        .forgot-container {
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
            margin-bottom: 50px;
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
    </style>
</head>
<body>
    <div class="forgot-container">
        <h2>Forgot Your Password?</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" class="btn-submit">Submit</button>
        </form>

        <a href="login.php" class="back-link">Go back to login</a>

        <?php if ($successMessage): ?>
            <div class="message success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorEmail): ?>
            <div class="message error-message">Email address not found.</div>
        <?php endif; ?>
    </div>
</body>
</html>
