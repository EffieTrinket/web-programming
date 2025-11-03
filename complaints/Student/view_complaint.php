<?php
session_start();

if (!isset($_SESSION["users"])) {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$usersObj = new Users();

// Get complaint ID from URL
$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$complaint = $usersObj->getComplaintById($complaint_id);

// Redirect if complaint not found
if (!$complaint) {
    echo "Complaint not found.";
    exit();
}

// Admin can update status and remarks
if ($_SESSION['users']['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? $complaint['status'];
    $remarks = $_POST['remarks'] ?? $complaint['resolution_remarks'];
    
    if ($usersObj->updateComplaintStatus($complaint_id, $status, $remarks)) {
        $complaint = $usersObj->getComplaintById($complaint_id); // Refresh data
        $success_msg = "Complaint updated successfully.";
    } else {
        $error_msg = "Failed to update complaint.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Complaint</title>
</head>
<body>

<h2>Complaint Details</h2>

<?php if (!empty($success_msg)) echo "<p style='color:green;'>$success_msg</p>"; ?>
<?php if (!empty($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>

<p><strong>Complaint ID:</strong> <?= htmlspecialchars($complaint['complaint_id']) ?></p>
<p><strong>Department Addressed:</strong> <?= htmlspecialchars($complaint['department'] ?? $complaint['department_id']) ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($complaint['category']) ?></p>
<p><strong>Description:</strong> <?= nl2br(htmlspecialchars($complaint['description'])) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($complaint['status']) ?></p>
<p><strong>Date Filed:</strong> <?= htmlspecialchars($complaint['date_filed']) ?></p>
<p><strong>Resolution Remarks:</strong> <?= nl2br(htmlspecialchars($complaint['resolution_remarks'])) ?></p>

<!-- Display attachment -->
<p><strong>Attachment:</strong>
<?php if (!empty($complaint['attachment'])): ?>
    <?php
        $ext = pathinfo($complaint['attachment'], PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif']);
    ?>
    <?php if ($isImage): ?>
        <br>
        <img src="../uploads/<?= htmlspecialchars($complaint['attachment']) ?>" alt="Attachment" style="max-width:300px;">
        <br>
    <?php endif; ?>
    <a href="../uploads/<?= htmlspecialchars($complaint['attachment']) ?>" target="_blank" download>View</a>
<?php else: ?>
    No attachment
<?php endif; ?>
</p>

<?php if ($_SESSION['users']['role'] === 'admin'): ?>
    <h3>Update Complaint (Admin Only)</h3>
    <form method="POST">
        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="Pending" <?= $complaint['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="In Progress" <?= $complaint['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Resolved" <?= $complaint['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>
        <br><br>
        <label for="remarks">Resolution Remarks:</label><br>
        <textarea name="remarks" id="remarks" rows="4" cols="50"><?= htmlspecialchars($complaint['resolution_remarks']) ?></textarea>
        <br><br>
        <input type="submit" value="Update Complaint">
    </form>
<?php endif; ?>

<br>
<a href="<?= $_SESSION['users']['role'] === 'admin' ? '../admin/admin_dashboard.php' : 'student_dashboard.php' ?>">Back</a>

</body>
</html>
