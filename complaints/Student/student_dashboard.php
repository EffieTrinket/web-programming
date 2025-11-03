<?php
session_start();

if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "student") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$registerObj = new Users();
$user_id = $_SESSION["users"]["user_id"];
$fname = $_SESSION["users"]["fname"];

$category = isset($_GET['category']) ? trim(htmlspecialchars($_GET['category'])) : '';
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0; // always define


if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($category) || $department_id > 0)) {
    // Filtered complaints
    $complaints = $registerObj->filterComplaints($user_id, $category, $department_id);
} else {
    // All complaints for this student
    $complaints = $registerObj->getComplaintsByUserId($user_id);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints</title>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($fname) ?></h2>
<p>This is where you can track your submitted complaints.</p>

<form method="GET" action="">
    <label>Category:</label>
    <select name="category">
        <option value="">All Categories</option>
        <?php
        $categories = ['Facility Issue', 'Academic Concern', 'Staff Conduct', 'Student Conduct', 'Health and Safety', 'Others'];
        foreach ($categories as $cat) {
            $selected = ($category === $cat) ? 'selected' : '';
            echo "<option value=\"$cat\" $selected>$cat</option>";
        }
        ?>
    </select>

    <label>Department:</label>
    <select name="department_id">
        <option value="0">All Departments</option>
        <?php
        $departments = [
            1 => "College of Engineering",
            2 => "College of Sports Science and Physical Education",
            3 => "External Studies Unit",
            4 => "College of Computing Studies",
            5 => "College of Nursing",
            6 => "College of Criminal Justice Education",
            7 => "College of Science and Mathematics",
            8 => "College of Liberal Arts",
            9 => "College of Agriculture",
            10 => "College of Home Economics",
            11 => "College of Teacher Education",
            12 => "College of Law",
            13 => "College of Architecture",
            14 => "College of Public Administration",
            15 => "College of Social Work and Community Development",
            16 => "College of Forestry and Environmental Studies",
            17 => "College of Asian and Islamic Studies",
            18 => "College of Medicine"
        ];

        foreach ($departments as $id => $name) {
            $selected = ($department_id == $id) ? 'selected' : '';
            echo "<option value=\"$id\" $selected>$name</option>";
        }
        ?>
    </select>

    <input type="submit" value="Filter">
</form>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Dapertment Addressed</th>
        <th>Category</th>
        <th>Status</th>
        <th>Date Filed</th>
        <th>Action</th>
    </tr>

    <?php 
        $id = 1;

        if (!empty($complaints)):
            foreach ($complaints as $complaint): 
    ?>
    <tr>
        <td><?= $id++ ?></td>
        <td><?= htmlspecialchars($complaint['department'] ?? '') ?></td>
        <td><?= htmlspecialchars($complaint["category"]) ?></td>
        <td><?= htmlspecialchars($complaint["status"]) ?></td>
        <td><?= htmlspecialchars($complaint["date_filed"]) ?></td>
        <td>
            <a href="view_complaint.php?id=<?= urlencode($complaint['complaint_id']) ?>">View</a>

        </td>
    </tr>
    <?php 
            endforeach; 
        else: 
    ?>
    <tr>
        <td colspan="5">No complaints found.</td>
    </tr>
    <?php endif; ?>
</table>

<br>
<a href="file_complaint.php">File a New Complaint</a><br>
<a href="../login&regis/logout.php">Logout</a>

</body>
</html>
