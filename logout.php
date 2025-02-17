<?php
session_start();
session_unset();  // Clear all session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: login.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        .btn {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-yes {
            background: #d9534f;
            color: white;
        }
        .btn-no {
            background: #5bc0de;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Are you sure you want to log out?</h2>
    <form action="logout.php" method="POST">
        <button type="submit" class="btn btn-yes">Yes, Logout</button>
        <a href="index.php" class="btn btn-no">No, Go Back</a>
    </form>
</div>

</body>
</html>
