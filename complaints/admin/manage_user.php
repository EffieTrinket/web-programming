<?php
session_start();
if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "admin") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$usersObj = new Users();

// Handle delete request
if (isset($_POST['delete_user_id'])) {
    $delete_id = intval($_POST['delete_user_id']);
    if ($usersObj->deleteUser($delete_id)) {
        $success_msg = "User deleted successfully.";
    } else {
        $error_msg = "Failed to delete user.";
    }
}

// Fetch all users
$users = $usersObj->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | Crimson</title>
<link rel="stylesheet" href="../Style/manage.css">
</head>
<body>
<div class="dashboard-container">

    <aside class="sidebar">
        <h2>Crimson</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <hr>
            <li><a href="#" class="active">Manage Users</a></li>
            <li><a href="../login&regis/logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 class= "topbar">Manage Users</h1>

        <!-- Success/Error messages -->
        <?php if (!empty($success_msg)) echo "<div class='success-box'>$success_msg</div>"; ?>
        <?php if (!empty($error_msg)) echo "<div class='error-box'>$error_msg</div>"; ?>

        <a href="add_user.php" class="submit-btn">+ Add New User</a>

        <?php
        // Handle search and department filter
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filter_department = isset($_GET['department']) ? intval($_GET['department']) : 0;

        // Fetch users based on search & department filter
        $users = $usersObj->getAllUsers($search, $filter_department);
        $departments = $usersObj->getDepartments(); // fetch all departments
        ?>
        <form method="GET" class="filter-section">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    
        <select name="department">
        <option value="0">-- All Departments --</option>
        <?php foreach($departments as $dept): ?>
            <option value="<?= $dept['department_id'] ?>" <?= ($filter_department == $dept['department_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($dept['department']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit">Filter</button>
    <a href="manage_user.php" class="clear-btn">Clear</a>
</form>
        <div class="table-section">
        <table border="1" cellpadding="10" cellspacing="0" class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>College</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['fname'] ." " .$user['mname'] ." ". $user['lname']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                            <td><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['course_name'] ?? '-') ?></td>
                            <td>

                            <div class="action-buttons">
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="view-btn">Edit</a>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="delete_user_id" value="<?= $user['user_id'] ?>">
                                    <input type="submit" value="Delete" class="delete-btn">
                            </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </main>
</div>
</body>
</html>
