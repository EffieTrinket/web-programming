<?php
session_start();

if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "admin") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$registerObj = new Users();
$fname = $_SESSION["users"]["fname"];

// Sanitize filter inputs
$category = isset($_GET['category']) ? trim(htmlspecialchars($_GET['category'])) : '';
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$complaint_id = isset($_GET['complaint_id']) ? trim(htmlspecialchars($_GET['complaint_id'])) : '';


// Get complaints using filter function
$complaints = $registerObj->filterComplaints($category, $department_id, $complaint_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Crimson</title>
    <link rel="stylesheet" href="../Style/admin_des.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Crimson</h2>
            <ul>
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="kpi.php">KPI Reports</a></li>
                <hr>
                <li><a href="manage_user.php">Manage Users</a></li>
                <li><a href="../login&regis/logout.php" class="logout" onclick="return confirm('Are you sure you want to log out?')">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Welcome, <?= htmlspecialchars($fname) ?></h1>
            </header>

            <section class="filter-section">
                <form method="GET" action="">


                    <label>Complaint ID:</label>
                    <input type="text" name="complaint_id" value="<?= isset($_GET['complaint_id']) ? htmlspecialchars($_GET['complaint_id']) : '' ?>" placeholder="Enter complaint ID">
<br><br>

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
                            $selected = ($department_id === $id) ? 'selected' : '';
                            echo "<option value=\"$id\" $selected>$name</option>";
                        }
                        ?>
                    </select>

                    <button type="submit">Filter</button>
                    <a href="admin_dashboard.php" class="clear-btn">Clear</a>
                </form>
            </section>

            <section class="table-section">
                <h2>Complaint Records</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Filed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($complaints)): ?>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td><?= htmlspecialchars($complaint['complaint_id']) ?></td>
                                    <td><?= htmlspecialchars($complaint['department'] ?? 'Unassigned') ?></td>
                                    <td><?= htmlspecialchars($complaint['category']) ?></td>
                                    <td class="status"><?= htmlspecialchars($complaint['status']) ?></td>
                                    <td><?= htmlspecialchars($complaint['date_filed']) ?></td>
                                    <td><a href="../Student/view_complaint.php?id=<?= urlencode($complaint['complaint_id']) ?>" class="view-btn">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="no-data">No complaints found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
