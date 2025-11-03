<?php
session_start();
require_once '../complaint/users.php';

$complaint = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $complaint['user_id'] = $_SESSION["users"]["user_id"];

    $department_id = isset($_POST['department']) ? intval($_POST['department']) : 0;
    if ($department_id < 1 || $department_id > 18) {
        $errors['department'] = "Please select a valid college department.";
    } else {
        $complaint['department_id'] = $department_id;
    }
    
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
            echo "Complaint filed successfully.";
        } else {
            echo "Something went wrong while saving your complaint.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Complaint</title>
</head>
<body>
     <?php if (!empty($errors)): ?>
        <div>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


    <form action="" method="POST" enctype="multipart/form-data">
        <label for="title">Category:</label>
        <select name= "category" id= "category">
            <option value="">----Select a Category----</option>
            <option value="Facility Issue"  <?php if(!empty($complaint['category']) && $complaint['category'] == 'Facility_Issue') echo 'selected'; ?>>Facility Issue</option>
            <option value="Academic Concern" <?php if(!empty($complaint['category']) && $complaint['category'] == 'Academic_Concern') echo 'selected'; ?>>Academic Concern</option>
            <option value="Staff Conduct" <?php if(!empty($complaint['category']) && $complaint['category'] == 'Staff_Conduct') echo 'selected'; ?>>Staff Conduct</option>
            <option value="Student Conduct" <?php if(!empty($complaint['category']) && $complaint['category'] == 'Student_Conduct') echo 'selected'; ?>>Student Conduct</option>
            <option value="Health and Safety" <?php if(!empty($complaint['category']) && $complaint['category'] == 'Health_and_Safety') echo 'selected'; ?>>Health and Safety</option>
            <option value="Others" <?php if(!empty($complaint['category']) && $complaint['category'] == 'Others') echo 'selected'; ?>>Others</option>
        </select><br><br>

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
        <br><br>

    
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" cols="50"
            placeholder="Describe your complaint here..."></textarea><br><br>
        <input type="file" value="attachment" name="attachment">
        <input type="submit" value="Submit Complaint">

        <a href="../Student/student_dashboard.php"><button type="button">Return</button></a>
    </form>
</body>
</html>