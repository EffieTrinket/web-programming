<?php
session_start();

if (!isset($_SESSION["users"]) || $_SESSION["users"]["role"] !== "admin") {
    header("Location: ../login&regis/login.php");
    exit();
}

require_once '../complaint/users.php';
$registerObj = new Users();
$fname = $_SESSION["users"]["fname"];

// Fetch KPI data
$totalMonth = $registerObj->getTotalComplaintsPastMonth();
$perDepartment = $registerObj->getComplaintsPerDepartment();
$perCategory = $registerObj->getComplaintsPerCategory();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KPI Dashboard | Crimson</title>
    <link rel="stylesheet" href="../Style/kpi.css">
</head>
<body>

<!-- Print Button (visible only on screen) -->
<button class="print-button no-print" onclick="window.print()">🖨️ Print Report</button>

<!-- Print Header (visible only when printing) -->
<div class="print-header">
    <h1>Crimson - KPI Dashboard Report</h1>
    <p class="print-date">Generated on: <?= date('F d, Y - h:i A') ?></p>
    <p class="print-date">Report by: <?= htmlspecialchars($fname) ?></p>
</div>

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Crimson</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="kpi_dashboard.php" class="active">KPI Reports</a></li>
            <hr>
            <li><a href="manage_user.php">Manage Users</a></li>
            <li><a href="../login&regis/logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <h1>KPI Dashboard — Welcome, <?= htmlspecialchars($fname) ?></h1>
        </header>

        <!-- KPI Cards -->
        <section class="table-section">
            <div class="card">
                <h2>Total Complaints (Past Month)</h2>
                <p class="kpi-number"><?= $totalMonth ?></p>
            </div>
        </section>

        <!-- Complaints Per Department -->
        <section class="table-section">
            <h2>Complaints Per Department</h2>
            <table>
                <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Complaints</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($perDepartment as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Complaints Per Category -->
        <section class="table-section">
            <h2>Complaints Per Category</h2>
            <table>
                <thead>
                <tr>
                    <th>Category</th>
                    <th>Total Complaints</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($perCategory as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

<!-- Print Footer (visible only when printing) -->
<div class="print-footer">
    <p>Crimson Complaint Management System | Confidential Report | Page printed on <?= date('F d, Y') ?></p>
</div>

</body>
</html>