<?php
session_start();
if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "admin") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$usersObj = new Users();
$database = new Database();
$conn = $database->connect();

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = $usersObj->getUserById($user_id);

if (!$user) {
    echo "User not found.";
    exit();
}

// Load departments
$departmentsStmt = $conn->query("SELECT * FROM departments ORDER BY department ASC");
$departments = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Load courses based on user's department
$courses = [];
if ($user['department_id']) {
    $coursesStmt = $conn->prepare("SELECT * FROM courses WHERE department_id = ?");
    $coursesStmt->execute([$user['department_id']]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $department_id = intval($_POST['department']);
    $course_id = intval($_POST['course']);

    // Basic validation
    if (empty($fname) || empty($lname) || empty($email) || empty($role)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $updateData = [
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'email' => $email,
            'role' => $role,
            'department_id' => $department_id > 0 ? $department_id : null,
            'course_id' => $course_id > 0 ? $course_id : null,
        ];

        if ($usersObj->updateUser($user_id, $updateData)) {
            $success_msg = "User updated successfully.";
            $user = $usersObj->getUserById($user_id); // refresh data
        } else {
            $errors[] = "Failed to update user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User | Crimson</title>
<link rel="stylesheet" href="../Style/edit.css">
<script>
function loadCourses(departmentId) {
    window.location.href = "edit_user.php?id=<?= $user_id ?>&department=" + departmentId;
}
</script>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Crimson</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_user.php">Manage Users</a></li>
            <li><a href="../login&regis/logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Edit User</h1>

        <?php if (!empty($success_msg)) echo "<div class='success-box'>$success_msg</div>"; ?>
        <?php if (!empty($errors)) echo "<div class='error-box'>" . implode('<br>', $errors) . "</div>"; ?>

        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="fname" value="<?= htmlspecialchars($user['fname']) ?>" required>

            <label>Middle Name:</label>
            <input type="text" name="mname" value="<?= htmlspecialchars($user['mname']) ?>">

            <label>Last Name:</label>
            <input type="text" name="lname" value="<?= htmlspecialchars($user['lname']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
            </select>

            <label>Department:</label>
            <select name="department" onchange="this.form.submit()">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>" <?= $user['department_id'] == $dept['department_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['department']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Course :</label>
            <select name="course">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['course_id'] ?>" <?= $user['course_id'] == $course['course_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="form-buttons">
                <input type="submit" value="Update User" class="submit-btn">
                <a href="manage_user.php" class="return-btn">Return</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
