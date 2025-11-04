<?php
session_start();
require_once '../complaint/users.php';

$complaint = [];
$errors = [];

$database = new Database();
$conn = $database->connect();

// Load departments
$departmentsStmt = $conn->query("SELECT * FROM departments ORDER BY department ASC");
$departments = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Detect selected department
$department_id = isset($_POST['department']) ? intval($_POST['department']) : 0;
$course_id = isset($_POST['course']) ? intval($_POST['course']) : 0;

// Load courses based on selected department
$courses = [];
if ($department_id > 0) {
    $coursesStmt = $conn->prepare("SELECT * FROM courses WHERE department_id = ?");
    $coursesStmt->execute([$department_id]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $complaint['user_id'] = $_SESSION["users"]["user_id"];

    $department_id = isset($_POST['department']) ? intval($_POST['department']) : 0;
    if ($department_id < 1 || $department_id > 18) {
        $errors['department'] = "Please select a valid college department.";
    } else {
        $complaint['department_id'] = $department_id;
    }
    
    $complaint['course_id'] = $course_id > 0 ? $course_id : null;
    $complaint['category'] = trim(htmlspecialchars($_POST['category']));
    $complaint['description'] = trim(htmlspecialchars($_POST['description']));

    if (empty($complaint['category'])) {
        $errors[] = "Category is required.";
    }

    if (empty($complaint['description']) || strlen($complaint['description']) < 10) {
        $errors[] = "Description is required and must be at least 10 characters.";
    }

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $fileName = time() . "_" . basename($_FILES['attachment']['name']);
    $targetPath = "../uploads/" . $fileName;
    move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath);
    $complaint['attachment'] = $fileName;
    } 
    else {
    $complaint['attachment'] = null;
    }


    if (empty($errors)) {
        $registerObj = new Users();
        $success = $registerObj->fileComplaint($complaint);
        if ($success) {
        echo '<p style="background-color: green; color: white; padding: 20px;text-align: center;">
                Complaint filed successfully.
              </p>';
    } else {
        echo '<p style="background-color: red; color: white; padding: 20px; text-align: center;">
                Something went wrong while saving your complaint.
              </p>';
    }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Complaint | Crimson</title>
<link rel="stylesheet" href="../Style/file.css">
</head>
<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <h2>Crimson</h2>
        <ul>
            <li><a href="../Student/student_dashboard.php" class="back-btn">My Complaints</a></li>
            <li><a href="../login&regis/logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">

        <div class = "lalagyan">
        <h1>File a New Complaint</h1>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="complaint-form">
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">----Select a Category----</option>
                <option value="Facility Issue" <?= (!empty($complaint['category']) && $complaint['category'] == 'Facility Issue') ? 'selected' : '' ?>>Facility Issue</option>
                <option value="Academic Concern" <?= (!empty($complaint['category']) && $complaint['category'] == 'Academic Concern') ? 'selected' : '' ?>>Academic Concern</option>
                <option value="Staff Conduct" <?= (!empty($complaint['category']) && $complaint['category'] == 'Staff Conduct') ? 'selected' : '' ?>>Staff Conduct</option>
                <option value="Student Conduct" <?= (!empty($complaint['category']) && $complaint['category'] == 'Student Conduct') ? 'selected' : '' ?>>Student Conduct</option>
                <option value="Health and Safety" <?= (!empty($complaint['category']) && $complaint['category'] == 'Health and Safety') ? 'selected' : '' ?>>Health and Safety</option>
                <option value="Others" <?= (!empty($complaint['category']) && $complaint['category'] == 'Others') ? 'selected' : '' ?>>Others</option>
            </select>

            <label for="department">Department:</label>
            <select name="department" id="department" required onchange="this.form.submit()">
                <option value="">-- Select College Department --</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['department_id'] ?>" <?= ($department_id == $dept['department_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dept['department']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label for="course">Course (optional):</label>
            <select name="course" id="course" <?= ($department_id == 0) ? 'disabled' : '' ?>>
                <option value="">-- Select Course (Optional) --</option>
                <?php foreach ($courses as $course): ?>
                <option value="<?= $course['course_id'] ?>" <?= ($course_id == $course['course_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['course_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="6" placeholder="Describe your complaint here..." required><?= htmlspecialchars($complaint['description'] ?? '') ?></textarea>

            <label for="attachment">Attachment (optional):</label>
            <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf">
        
            <div class="form-buttons">
                <input type="submit" value="Submit Complaint" class="submit-btn">
                <a href="../Student/student_dashboard.php" class="return-btn">Return</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
