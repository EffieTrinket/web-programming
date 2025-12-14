<?php
session_start();
require_once '../complaint/users.php';

//INCLUDE PHPMAILER 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require '../complaint/PHPMailer.php';
require '../complaint/Exception.php';  
require '../complaint/SMTP.php';       



$complaint = [];
$errors = [];
$notification = '';
$notification_type = '';

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

// EMAIL SENDING FUNCTION 
function sendEmail($to, $subject, $body, $replyTo = null) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'crimsoncolleges@gmail.com'; 
        $mail->Password = 'fiqfzycsqbqnbjoo'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Sender and recipient
        $mail->setFrom('your-email@gmail.com', 'Crimson Complaint System');
        $mail->addAddress($to);
        
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }
        
        // Email content
        $mail->isHTML(true); // Plain text email
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
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
        $complaint_id = $registerObj->fileComplaint($complaint);

        
        if ($complaint_id) {
            // COMPLAINT HISTORY  
            $historyStmt = $conn->prepare("
            INSERT INTO complaint_history 
            (complaint_id, status, updated_by, notes)
            VALUES (:complaint_id, :status, :updated_by, :notes)
");

            $historyStmt->execute([
                ':complaint_id' => $complaint_id,
                ':status' => 'Pending',
                ':updated_by' => $_SESSION['users']['user_id'],
                ':notes' => 'Complaint submitted by student'
]);
        

            // Get student information
            $student_name = $_SESSION["users"]["fname"] . " " . $_SESSION["users"]["lname"];
            $student_email = $_SESSION["users"]["email"] ?? 'student@example.com';
            
            // Get department name
            $deptStmt = $conn->prepare("SELECT department FROM departments WHERE department_id = ?");
            $deptStmt->execute([$department_id]);
            $deptInfo = $deptStmt->fetch(PDO::FETCH_ASSOC);
            $department_name = $deptInfo['department'] ?? 'Unknown';
            
            
            // ============ SEND EMAILS ============
            
            // 1. EMAIL TO ADMIN
            $admin_email = "crimsoncolleges@gmail.com", "dixontrumatajr@gmail.com"; // Admin email address
            $admin_subject = "New Complaint Submitted - ID: #" . $complaint_id;
            $admin_body = '
            <html>
                <body style="font-family: Arial, sans-serif; background-color: #ffffff; padding: 20px;">
                <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px;">
                    <div style="background-color: #8B0000; padding: 15px; color: white; border-radius: 8px 8px 0 0; text-align: center;">
                        <h2 style="margin: 0;">New Complaint Submitted</h2>
                        <p style="margin: 0;">Complaint ID: #' . $complaint_id . '</p>
                    </div>

            <div style="padding: 20px;">
                <p><strong>Student:</strong> ' . $student_name . '</p>
                <p><strong>Email:</strong> ' . $student_email . '</p>
                <p><strong>Department Addressed:</strong> ' . $department_name . '</p>
                <p><strong>Category:</strong> ' . $complaint["category"] . '</p>

            <div style="margin-top: 20px; padding: 15px; background-color: #f8d7da; border-left: 5px solid #8B0000;">
                <p style="margin: 0;"><strong>Description:</strong><br>' . nl2br($complaint["description"]) . '</p>
            </div>

                <p style="margin-top: 25px;">Please log in to the admin panel to review and take action.</p>
            </div>

            <div style="background-color: #8B0000; color: white; padding: 10px; text-align: center; border-radius: 0 0 8px 8px;">
            <small>&copy; Crimson Complaint Management System</small>
            </div>
            </div>
            </body>
            </html>';


            
            sendEmail($admin_email, $admin_subject, $admin_body, $student_email);
            
            // 2. EMAIL TO STUDENT (Confirmation)
            $student_subject = "Complaint Submitted Successfully - ID: #" . $complaint_id;
            $student_body = '
            <html>
                <body style="font-family: Arial, sans-serif; background-color: #ffffff; padding: 20px;">
                <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px;">
                <div style="background-color: #8B0000; padding: 15px; color: white; border-radius: 8px 8px 0 0; text-align: center;">
                    <h2 style="margin: 0;">Complaint Submitted Successfully</h2>
                    <p style="margin: 0;">Complaint ID: #' . $complaint_id . '</p>
                </div>

            <div style="padding: 20px;">
                <p>Dear <strong>' . $student_name . '</strong>,</p>
                <p>Your complaint has been received and is now under review.</p>

                <h3 style="color: #8B0000;">Complaint Details</h3>
                <p><strong>Category:</strong> ' . $complaint["category"] . '</p>
                <p><strong>Department Adressed:</strong> ' . $department_name . '</p>
                <p><strong>Status:</strong> Pending</p>

            <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-left: 5px solid #8B0000;">
                <p style="margin: 0;"><strong>Your Description:</strong><br>' . nl2br($complaint["description"]) . '</p>
            </div>

            <p style="margin-top: 25px;">You will receive email updates once your complaint progresses.</p>
            <p>Thank you for your patience.</p>
            </div>

            <div style="background-color: #8B0000; color: white; padding: 10px; text-align: center; border-radius: 0 0 8px 8px;">
            <small>&copy; Crimson Complaint Management System</small>
            </div>
            </div>
            </body>
            </html>';

            sendEmail($student_email, $student_subject, $student_body);
            
            // =====================================
            
            // success notification
            $notification = "Complaint filed successfully! Confirmation email sent to " . $student_email;
            $notification_type = "success";
            
        } else {
            // error notification
            $notification = "Something went wrong while saving your complaint.";
            $notification_type = "error";
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
<style>
    /* ============ NOTIFICATION STYLES ============ */

.notification {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    width: 100%;
    box-sizing: border-box;
    animation: fadeSlideDown 0.45s ease-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    position: relative;
}

/* SUCCESS */
.notification.success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 6px solid #2e7d32;
}

/* ERROR */
.notification.error {
    background: #ffebee;
    color: #c62828;
    border-left: 6px solid #c62828;
}

/* SLIDE + FADE ANIMATION */
@keyframes fadeSlideDown {
    from {
        opacity: 0;
        transform: translateY(-12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification.fade-out {
    animation: fadeOut 0.6s ease forwards;
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

</style>
</head>
<body>

<div class="dashboard-container">

    <aside class="sidebar">
        <h2>Crimson</h2>
        <hr>
        <ul>
            <li><a href="../Student/student_dashboard.php" class="back-btn">My Complaints</a></li>
        </ul>
    </aside>

    <main class="main-content">

        <div class="lalagyan">
            <h1>File a New Complaint</h1>
        </div>

        <!-- ============ SYSTEM NOTIFICATION DISPLAY ============ -->
        <?php if (!empty($notification)): ?>
        <div class="notification <?= $notification_type ?>">
            <?= htmlspecialchars($notification) ?>
        </div>
        <?php endif; ?>
        <!-- ============ SYSTEM NOTIFICATION END ============ -->

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
        
            <br><br>

            <div class="form-buttons">
                <input type="submit" value="Submit Complaint" class="submit-btn" onclick ="return confirm('Are you sure you want to submit this complaint?')">
                <a href="../Student/student_dashboard.php" class="return-btn">Return</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>