<?php
session_start();

if (isset($_SESSION["users"])) {
    if ($_SESSION["users"]['role'] == 'admin') {
        header('location: ../admin/admin_dashboard.php');
        exit();
    } 
    if ($_SESSION["users"]['role'] == 'student'){
        header('location: ../Student/student_dashboard.php');
        exit();
    }
}

require_once('../complaint/account.php');
$account = new Account();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account->email = trim(htmlentities($_POST['email']));
    $account->password = trim(htmlentities($_POST['password']));

    if (empty($account->email) && empty($account->password)) {
        $error = "Please enter your email and password.";
    } 
    else if (empty($account->email)) {
        $error = "Please enter your email.";
    }
    else if (empty($account->password)) {
        $error = "Please enter your password.";
    }
    else {
        if ($account->login()) {
            $_SESSION["users"] = $account->getUsersByEmail();

            if ($_SESSION["users"]["role"] == "admin") {
                header('location: ../admin/admin_dashboard.php');
                exit();
            } else if ($_SESSION["users"]["role"] == "student") {
                header('location: ../Student/student_dashboard.php');
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
</head>
<body>

    <div class="container" id="login-form">
    <h2>Log In</h2>
    <form action="" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php if(isset($_POST['email'])) { echo $_POST['email']; } ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" value="<?php if(isset($_POST['password'])) { echo $_POST['password']; } ?>">
        <p><?= $error ?? ""?></p>

        <input type="submit" value="Log In">
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>