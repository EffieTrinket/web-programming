<?php
require_once '../complaint/users.php';
$registerObj = new Users();

$register = [];
$errors = [];

$conn = $registerObj->conn;

$department_id = isset($_POST['department']) ? intval($_POST['department']) : 0;
$course_id = isset($_POST['course']) ? intval($_POST['course']) : 0;

$departmentsStmt = $conn->query("SELECT * FROM departments ORDER BY department ASC");
$departments = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);

$courses = [];
if ($department_id > 0) {
    $coursesStmt = $conn->prepare("SELECT * FROM courses WHERE department_id = ?");
    $coursesStmt->execute([$department_id]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $register['lname'] = trim(htmlspecialchars($_POST['lname']));
    $register['fname'] = trim(htmlspecialchars($_POST['fname']));
    $register['mname'] = trim(htmlspecialchars($_POST['mname']));
    $register['email'] = trim(htmlspecialchars($_POST['email']));
    $register['password'] = trim(htmlspecialchars($_POST['password']));
    $register['role'] = 'student';

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

    if (empty($_POST['department']) || empty($_POST['course'])) {
    $errors[] = "Please select both department and course.";
    }


    if (empty($errors)) {
        $registerObj->lname = $register['lname'];
        $registerObj->fname = $register['fname'];
        $registerObj->mname = $register['mname'];
        $registerObj->email = $register['email'];
        $registerObj->password = $register['password'];
        $registerObj->role = $register['role'];
        $registerObj->department_id = $department_id; 
        $registerObj->course_id = $_POST['course'];

        if ($registerObj->registerUser()) {
            echo "Registration successful";
        } else {
            echo "Registration failed. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crimson | Add User</title>
    <link rel="stylesheet" href="../Style/reg_des.css">
</head>
<body>

    <div class="register-container">
        <form action="" method="POST" class="register-form">

            <a href="manage_user.php" class="return">← Return</a>
            <h2> Registration</h2>
        

            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" value="<?=$register['fname'] ?? ''?>">
                <p class="error"><?= $errors["fname"] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="mname">Middle Name</label>
                <input type="text" id="mname" name="mname">
            </div>

            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" value="<?=$register['lname'] ?? ''?>">
                <p class="error"><?= $errors["lname"] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="department">College Department</label>
                <select name="department" id="department" onchange="this.form.submit()">
                    <option value="">-- Select College Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>" <?= ($department_id == $dept['department_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['department']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="error"><?= $errors['department'] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="course">Course</label>
                <select name="course" id="course" <?= ($department_id == 0) ? 'disabled' : '' ?>>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>" <?= ($course_id == $course['course_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="error"><?= $errors['course'] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?=$register['email'] ?? ''?>">
                <p class="error"><?= $errors["email"] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <p class="error"><?= $errors["password"] ?? '' ?></p>
            </div>

            <div class="form-group">
                <label for="conpassword">Confirm Password</label>
                <input type="password" id="conpassword" name="conpassword">
            </div>

            <button type="submit" class="btn-register">Register</button>
        </form>
    </div>
</body>
</html>
