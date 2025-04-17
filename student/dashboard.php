<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student's sections and courses
$enrollments_query = "SELECT s.name as section_name, c.course_name, u.username as teacher_name
                     FROM student_assignments sa
                     JOIN sections s ON sa.section_id = s.id
                     JOIN courses c ON sa.course_id = c.id
                     JOIN users u ON sa.assigned_by = u.id
                     WHERE sa.student_id = ? AND sa.status = 'active'";
$stmt = $conn->prepare($enrollments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrollments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="my-sections.php" class="nav-link">My Sections</a></li>
                <li><a href="view-assignments.php" class="nav-link">View Assignments</a></li>
                <li><a href="profile.php" class="nav-link">My Profile</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?></h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>My Sections & Courses</h3>
                    <div class="enrollment-list">
                        <?php if ($enrollments->num_rows > 0): ?>
                            <?php while($row = $enrollments->fetch_assoc()): ?>
                                <div class="enrollment-item">
                                    <h4>Section <?php echo htmlspecialchars($row['section_name']); ?></h4>
                                    <p>Course: <?php echo htmlspecialchars($row['course_name']); ?></p>
                                    <p>Teacher: <?php echo htmlspecialchars($row['teacher_name']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No sections assigned yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
