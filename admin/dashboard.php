<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get detailed user counts
$users_query = "SELECT role, status, COUNT(*) as count 
                FROM users 
                WHERE status = 'active'
                GROUP BY role, status";
$users_result = $conn->query($users_query);

$users_count = [
    'teacher' => 0,
    'student' => 0,
    'admin' => 0
];

while($row = $users_result->fetch_assoc()) {
    $users_count[$row['role']] = $row['count'];
}

// Get active sections count
$sections_query = "SELECT COUNT(*) as count FROM sections WHERE status = 'open'";
$sections_result = $conn->query($sections_query);
$active_sections = $sections_result->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="manage-users.php" class="nav-link">Manage Users</a></li>
                <li><a href="manage-sections.php" class="nav-link">Manage Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h1>Admin Dashboard</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Active Users</h3>
                    <div class="stats">
                        <p>Teachers: <span class="count"><?php echo $users_count['teacher']; ?></span></p>
                        <p>Students: <span class="count"><?php echo $users_count['student']; ?></span></p>
                        <p>Admins: <span class="count"><?php echo $users_count['admin']; ?></span></p>
                    </div>
                    <a href="manage-users.php" class="card-link">Manage Users</a>
                </div>
                <div class="card">
                    <h3>Active Sections</h3>
                    <p class="count"><?php echo $active_sections; ?></p>
                    <a href="manage-sections.php" class="card-link">Manage Sections</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
