<?php
session_start();

if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "admin") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$registerObj = new Users();

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$complaint_id = $_GET['id'];
$complaint = $registerObj->getComplaintById($complaint_id); // create function in Users

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST["status"];
    $remarks = $_POST["remarks"];

    $registerObj->updateComplaintStatus($complaint_id, $status, $remarks); // create function in Users
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Complaint</title></head>
<body>

<h2>Edit Complaint #<?= htmlspecialchars($complaint_id) ?></h2>

<form method="POST">
    <label>Status:</label>
    <select name="status">
        <option value="Submitted" <?= $complaint['status'] == 'Submitted' ? 'selected' : '' ?>>Submitted</option>
        <option value="In Review" <?= $complaint['status'] == 'In Review' ? 'selected' : '' ?>>In Review</option>
        <option value="Resolved" <?= $complaint['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
        <option value="Rejected" <?= $complaint['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
    </select>
    <br><br>
    <label>Resolution Remarks:</label>
    <textarea name="remarks" rows="4" cols="40"><?= htmlspecialchars($complaint['resolution_remarks'] ?? '') ?></textarea>
    <br><br>
    <button type="submit">Save</button>
</form>

</body>
</html>
