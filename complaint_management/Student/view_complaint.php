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

// Handle delete request (for both admin and student)
if (isset($_POST['delete_complaint_id'])) {
    $delete_id = intval($_POST['delete_complaint_id']); // sanitize input
    if ($usersObj->deleteComplaint($delete_id)) {
        // redirect to dashboard with success message
        $redirect_url = $_SESSION['users']['role'] === 'admin' ? "../admin/admin_dashboard.php" : "student_dashboard.php";
        header("Location: $redirect_url?success=" . urlencode("Complaint deleted successfully."));
        exit();
    } else {
        $error_msg = "Failed to delete complaint.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Complaint | Crimson</title>
<link rel="stylesheet" href="../Style/view.css">
</head>
<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <h2>Crimson</h2>
       <hr>
    </aside>

    

    <main class="main-content">
        <div class="top-buttons">
        <a href="<?= $_SESSION['users']['role'] === 'admin' ? '../admin/admin_dashboard.php' : 'student_dashboard.php' ?>" class="back-btn">← Return to Dashboard</a>
        </div>

        <?php if (!empty($success_msg)): ?>
            <p class="success-box"><?= htmlspecialchars($success_msg) ?></p>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
            <p class="error-box"><?= htmlspecialchars($error_msg) ?></p>
        <?php endif; ?>

        <h1>Complaint Details</h1>

        <?php if (!empty($success_msg)) echo "<p style='color:green;'>$success_msg</p>"; ?>
        <?php if (!empty($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>

        <div class="table-section">
            <p><strong>Complaint ID:</strong> <?= htmlspecialchars($complaint['complaint_id']) ?></p> <br>
            <p><strong>Sender Department:</strong> <?= htmlspecialchars($complaint['sender_department'] ?? 'N/A') ?></p><br>
            <p><strong>Sender Course:</strong> <?= htmlspecialchars($complaint['sender_course'] ?? 'N/A') ?></p><br>
            <hr>
            <p><strong>Department Addressed:</strong> <?= htmlspecialchars($complaint['target_department'] ?? 'N/A') ?></p><br>
            <p><strong>Course Addressed:</strong> <?= htmlspecialchars($complaint['target_course'] ?? 'N/A') ?></p><br>
            <p><strong>Category:</strong> <?= htmlspecialchars($complaint['category']) ?></p><br>
            <div class="description-box">
            <p><strong>Description:</strong></p>
            <?= nl2br(htmlspecialchars($complaint['description'])) ?>
            </div>


            <div class="description-box">
            <p><strong>Attachment:</strong>
            <?php if (!empty($complaint['attachment'])): ?>
                <?php
                    $ext = pathinfo($complaint['attachment'], PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif']);
                ?>
                <?php if ($isImage): ?>
                <br>
                <img src="../uploads/<?= htmlspecialchars($complaint['attachment']) ?>" 
                alt="Attachment" 
                class="attachment-img">
                <br>
            <?php endif; ?>

            <?php else: ?>
                No attachment
            <?php endif; ?>
            </p>
            </div>

            <p><strong>Date Filed:</strong> <?= htmlspecialchars($complaint['date_filed']) ?></p> <br>
            <p><strong>Status:</strong> <?= htmlspecialchars($complaint['status']) ?></p> <br>
            <p><strong>Resolution Remarks:</strong> 
            <div class="description-box">
            <?= nl2br(htmlspecialchars($complaint['resolution_remarks'])) ?></p>
            </div>
        </div>

            <?php if ($_SESSION['users']['role'] === 'admin' || $_SESSION['users']['role'] === 'student'): ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this complaint?');" style="margin-top:15px;">
                <input type="hidden" name="delete_complaint_id" value="<?= $complaint['complaint_id'] ?>">
                <input type="submit" value="Delete Complaint" class="delete-btn">
                </form>
            <?php endif; ?>

            <hr>
            
        <?php if ($_SESSION['users']['role'] === 'admin'): ?>
            <div class="table-section">
                <h3>Update Complaint</h3>
                <form method="POST">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="Pending" <?= $complaint['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $complaint['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Resolved" <?= $complaint['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                    <br><br>
                    <label for="remarks">Resolution Remarks:</label><br>
                    <div class="description-box">
                    <textarea name="remarks" id="remarks" rows="4" cols="50"><?= htmlspecialchars($complaint['resolution_remarks']) ?></textarea>
                    <br><br>
                    <input type="submit" value="Update Complaint" class="view-btn">
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
