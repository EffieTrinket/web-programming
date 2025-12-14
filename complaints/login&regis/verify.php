<?php
require_once '../complaint/users.php';
$registerObj = new Users();
$message = '';
$redirect = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($registerObj->verifyEmail($token)) {
        $message = "Your email has been verified! Redirecting to login...";
        $redirect = true;
    } else {
        $message = "Invalid verification link.";
    }
} else {
    $message = "No token provided.";
}

if ($redirect) {
    header("refresh:3;url=login.php"); // redirect after 3 seconds
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMSU | Email Verification</title>
    <style>
        /* WMSU-inspired colors */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .verify-container {
            background-color: #8C1C13; /* Crimson */
            color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px;
        }

        .verify-container h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .verify-container p {
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .verify-container a {
            display: inline-block;
            background-color: #FFD700; /* Gold */
            color: #8C1C13;
            text-decoration: none;
            font-weight: bold;
            padding: 12px 25px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .verify-container a:hover {
            background-color: #e6c200;
        }

        @media (max-width: 500px) {
            .verify-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h1>Email Verification</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <?php if (!$redirect): ?>
            <a href="login.php">Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
