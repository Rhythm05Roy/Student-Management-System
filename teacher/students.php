<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all assigned students with their details
$students_sql = "SELECT DISTINCT u.id, u.username, s.name as section_name, c.course_name
                 FROM student_assignments sa
                 JOIN users u ON sa.student_id = u.id
                 JOIN sections s ON sa.section_id = s.id
                 JOIN courses c ON sa.course_id = c.id
                 WHERE sa.assigned_by = ? AND sa.status = 'active'
                 ORDER BY u.username";
$stmt = $conn->prepare($students_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$students_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Students</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="students.php" class="nav-link active">My Students</a></li>
                <li><a href="assign-students.php" class="nav-link">Assign Students</a></li>
                <li><a href="sections.php" class="nav-link">View Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>My Students</h2>
            <div class="students-list">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Section</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td>Section <?php echo htmlspecialchars($student['section_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
