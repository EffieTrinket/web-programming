<?php
require_once '../complaint/users.php';
$registerObj = new Users();

$register = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $register['lname'] = trim(htmlspecialchars($_POST['lname']));
    $register['fname'] = trim(htmlspecialchars($_POST['fname']));
    $register['mname'] = trim(htmlspecialchars($_POST['mname']));
    $register['email'] = trim(htmlspecialchars($_POST['email']));
    $register['password'] = trim(htmlspecialchars($_POST['password']));
    $register['role'] = 'student';
    
    $department_id = isset($_POST['department']) ? intval($_POST['department']) : 0;

    if (empty($register['lname'])) {
        $errors['lname'] = "Last name is required.";
    }

    if (empty($register['fname'])) {
        $errors['fname'] = "First name is required.";
    }

    if (empty($register['email']) || !filter_var($register['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "A valid email is required.";
    } elseif ($registerObj->checkEmailExists($register['email'])) {
        $errors['email'] = "This email is already registered.";
    }

    if ($department_id < 1 || $department_id > 18) {
        $errors['department'] = "Please select a valid college department.";
    }

    if (empty($register['password']) || empty($_POST['conpassword']) || strlen($register['password']) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif ($register['password'] !== trim(htmlspecialchars($_POST['conpassword']))) {
        $errors['password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $registerObj->lname = $register['lname'];
        $registerObj->fname = $register['fname'];
        $registerObj->mname = $register['mname'];
        $registerObj->email = $register['email'];
        $registerObj->password = $register['password'];
        $registerObj->role = $register['role'];
        $registerObj->department_id = $department_id; 

        if ($registerObj->registerUser()) {
            header("Location: login.php");
            exit();
        } else {
            echo "Registration failed. Please try again.";
        }
    }
}
?>


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <div class="container" id="register-form">
    <form action="" method="POST">
        <h2>Register</h2>
        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" value="<?=$register['lname'] ?? ""?>">
        <p><?= $errors["lname"] ?? ""?></p>

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname"  value="<?=$register['fname'] ?? ""?>">
        <p><?= $errors["fname"] ?? ""?></p>
        
        <label for="mname">Middle Name:</label>
        <input type="text" id="mname" name="mname" > <br><br>

       <select name="department" id="department" required>
            <option value="">-- Select College Department --</option>
            <option value="1" <?= (isset($department_id) && $department_id == 1) ? 'selected' : '' ?>>College of Engineering</option>
            <option value="2" <?= (isset($department_id) && $department_id == 2) ? 'selected' : '' ?>>College of Sports Science and Physical Education</option>
            <option value="3" <?= (isset($department_id) && $department_id == 3) ? 'selected' : '' ?>>External Studies Unit</option>
            <option value="4" <?= (isset($department_id) && $department_id == 4) ? 'selected' : '' ?>>College of Computing Studies</option>
            <option value="5" <?= (isset($department_id) && $department_id == 5) ? 'selected' : '' ?>>College of Nursing</option>
            <option value="6" <?= (isset($department_id) && $department_id == 6) ? 'selected' : '' ?>>College of Criminal Justice Education</option>
            <option value="7" <?= (isset($department_id) && $department_id == 7) ? 'selected' : '' ?>>College of Science and Mathematics</option>
            <option value="8" <?= (isset($department_id) && $department_id == 8) ? 'selected' : '' ?>>College of Liberal Arts</option>
            <option value="9" <?= (isset($department_id) && $department_id == 9) ? 'selected' : '' ?>>College of Agriculture</option>
            <option value="10" <?= (isset($department_id) && $department_id == 10) ? 'selected' : '' ?>>College of Home Economics</option>
            <option value="11" <?= (isset($department_id) && $department_id == 11) ? 'selected' : '' ?>>College of Teacher Education</option>
            <option value="12" <?= (isset($department_id) && $department_id == 12) ? 'selected' : '' ?>>College of Law</option>
            <option value="13" <?= (isset($department_id) && $department_id == 13) ? 'selected' : '' ?>>College of Architecture</option>
            <option value="14" <?= (isset($department_id) && $department_id == 14) ? 'selected' : '' ?>>College of Public Administration</option>
            <option value="15" <?= (isset($department_id) && $department_id == 15) ? 'selected' : '' ?>>College of Social Work and Community Development</option>
            <option value="16" <?= (isset($department_id) && $department_id == 16) ? 'selected' : '' ?>>College of Forestry and Environmental Studies</option>
            <option value="17" <?= (isset($department_id) && $department_id == 17) ? 'selected' : '' ?>>College of Asian and Islamic Studies</option>
            <option value="18" <?= (isset($department_id) && $department_id == 18) ? 'selected' : '' ?>>College of Medicine</option>
        </select>
            <p><?= $errors["department"] ?? ""?></p>


        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email"  value="<?=$register['email'] ?? ""?>">
        <p><?= $errors["email"] ?? ""?></p>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password"  value="<?=$register['password'] ?? ""?>" >
        <p><?= $errors["password"] ?? ""?></p>

        <label for="password">Confirm Password:</label>
        <input type="password" id="conpassword" name="conpassword"  value="<?=$register['conpassword'] ?? ""?>">
        <p><?= $errors["password"] ?? ""?></p>
        
        <input type="submit" value="Register">

        <p>Already have an account? <a href="login.php">Log in here</a></p>
    </form>
    </div>
</body>
</html>