<?php
session_start();

if (!isset($_SESSION["users"])) {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
require '../complaint/PHPMailer.php';
require '../complaint/SMTP.php';
require '../complaint/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$usersObj = new Users();

// Get complaint ID from URL
$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$complaint = $usersObj->getComplaintById($complaint_id);
if (!$complaint) {
    echo "Complaint not found.";
    exit();
}

// Admin updates complaint
if ($_SESSION['users']['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'] ?? $complaint['status'];
    $remarks = $_POST['remarks'] ?? $complaint['resolution_remarks'];

    if ($usersObj->updateComplaintStatus($complaint_id, $status, $remarks)) {
         // --- LOG HISTORY ---
        $historyStmt = $usersObj->conn->prepare("
            INSERT INTO complaint_history
            (complaint_id, status, updated_by, notes)
            VALUES (:complaint_id, :status, :updated_by, :notes)
        ");

        $historyStmt->execute([
            ':complaint_id' => $complaint_id,
            ':status' => $status,
            ':updated_by' => $_SESSION['users']['user_id'],
            ':notes' => $remarks
        ]);

        // Refresh complaint
        $complaint = $usersObj->getComplaintById($complaint_id);

        // Student info
        $student_name = $complaint['fname'] . " " . $complaint['lname'];
        $student_email = $complaint['email'];

        // Email content
        $email_subject = "Your Complaint Has Been Updated - ID #" . $complaint_id;

        $email_body = '
        <html>
        <body style="font-family: Arial; background: #ffffff; padding: 20px;">
            <div style="max-width: 600px; margin: auto; border:1px solid #ddd; border-radius: 8px;">
                <div style="background:#8B0000; color:white; padding:15px; text-align:center; border-radius:8px 8px 0 0;">
                    <h2 style="margin:0;">Complaint Update Notification</h2>
                    <p style="margin:0;">Complaint ID: #' . $complaint_id . '</p>
                </div>
                <div style="padding:20px;">
                    <p>Dear <strong>' . $student_name . '</strong>,</p>
                    <p>Your complaint has been updated by the administrator.</p>
                    <h3 style="color:#8B0000;">Updated Details</h3>
                    <p><strong>Status:</strong> ' . htmlspecialchars($status) . '</p>
                    <p><strong>Resolution Remarks:</strong></p>
                    <div style="padding:15px; background:#fff3cd; border-left:5px solid #8B0000; margin-top:10px;">
                        ' . nl2br(htmlspecialchars($remarks)) . '
                    </div>
                    <p style="margin-top:20px;">You may log in to your dashboard to view the full complaint details.</p>
                    <p>Thank you,<br><strong>Crimson Complaint Management System</strong></p>
                </div>
                <div style="background:#8B0000; color:white; padding:10px; text-align:center; border-radius:0 0 8px 8px;">
                    <small>&copy; ' . date("Y") . ' Crimson Complaint Management System</small>
                </div>
            </div>
        </body>
        </html>';

        // -------------------------
        // PHPMailer setup
        // -------------------------
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'crimsoncolleges@gmail.com'; // Replace with your Gmail
            $mail->Password   = 'fiqfzycsqbqnbjoo';   // Replace with App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('youremail@gmail.com', 'Crimson Complaint System');
            $mail->addAddress($student_email, $student_name);

            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;

            $mail->send();
            $success_msg = "Complaint updated successfully. Email sent to " . $student_email;

        } catch (Exception $e) {
            $success_msg = "Complaint updated, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $error_msg = "Failed to update complaint.";
    }
}

// Handle delete
if (isset($_POST['delete_complaint_id'])) {
    $delete_id = intval($_POST['delete_complaint_id']);
    if ($usersObj->deleteComplaint($delete_id)) {
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
<link rel="stylesheet" href="../Style/kpi.css">
</head>
<body>

<button class="print-button no-print" onclick="window.print()">🖨️ Print Complaint</button>

<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Crimson</h2>
        <ul>
            <div class="top-buttons">
            <li><a href="<?= $_SESSION['users']['role'] === 'admin' ? '../admin/admin_dashboard.php' : 'student_dashboard.php' ?>" class="back-btn"><strong>← Return to Dashboard</strong></a></li>
            </div>
        </aside>

    <main class="main-content">
        
        <?php if (!empty($success_msg)): ?>
            <p class="success-box"><?= htmlspecialchars($success_msg) ?></p>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <p class="error-box"><?= htmlspecialchars($error_msg) ?></p>
        <?php endif; ?>

        <h1>Complaint Details</h1>
        <div class="table-section">
            <p><strong>Complaint ID:</strong> <?= htmlspecialchars($complaint['complaint_id']) ?></p>
            <p><strong>Department Addressed:</strong> <?= htmlspecialchars($complaint['sender_department'] ?? 'N/A') ?></p>
            <p><strong>Course Addressed:</strong> <?= htmlspecialchars($complaint['sender_course'] ?? 'N/A') ?></p>
            <hr>
            <p><strong>Category:</strong> <?= htmlspecialchars($complaint['category']) ?></p>
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
                        alt="Attachment" class="attachment-img"><br>
                    <?php endif; ?>
                <?php else: ?>
                    No attachment
                <?php endif; ?>
                </p>
            </div>
            <p><strong>Date Filed:</strong> <?= htmlspecialchars($complaint['date_filed']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($complaint['status']) ?></p>
            <p><strong>Resolution Remarks:</strong></p>
            <div class="description-box"><?= nl2br(htmlspecialchars($complaint['resolution_remarks'])) ?></div>
        </div>

        <?php if ($_SESSION['users']['role'] === 'admin' || $_SESSION['users']['role'] === 'student'): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this complaint?');" class="no-print" style="margin-top:15px;">
                <input type="hidden" name="delete_complaint_id" value="<?= $complaint['complaint_id'] ?>">
                <input type="submit" value="Delete Complaint" class="delete-btn">
            </form>
        <?php endif; ?>

        <hr class="no-print">

        <?php if ($_SESSION['users']['role'] === 'admin'): ?>
        <div class="table-section no-print">
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
                </div>

                <br><br>

                <input type="submit" value="Update Complaint" class="view-btn" onclick ="return confirm('Are you sure you want to update this complaint?')">
            </form>
        </div>
        <?php endif; ?>

        <?php
$history = $usersObj->getComplaintHistory($complaint_id);
?>
<div class="table-section no-print">
    <h3>Complaint History Timeline</h3>
    <?php if (!empty($history)): ?>
        <div class="timeline">
            <?php foreach ($history as $row): ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="timeline-date"><?= date('F d, Y h:i A', strtotime($row['updated_at'])) ?></span>
                        <h4><?= htmlspecialchars($row['status']) ?></h4>
                        <p><?= nl2br(htmlspecialchars($row['notes'])) ?></p>
                        <small>Updated by: <?= htmlspecialchars($row['updated_by_name']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No history updates yet.</p>
    <?php endif; ?>
</div>
    </main>
</div>

<div class="print-footer">
    <p>Crimson Complaint Management System | Confidential Document | Printed on <?= date('F d, Y') ?></p>
</div>

</body>
</html>
