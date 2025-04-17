<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sections_query = "SELECT DISTINCT s.name as section_name, s.status,
                  GROUP_CONCAT(DISTINCT c.course_name) as courses,
                  GROUP_CONCAT(DISTINCT u.username) as teachers
                  FROM student_assignments sa
                  JOIN sections s ON sa.section_id = s.id
                  JOIN courses c ON sa.course_id = c.id
                  JOIN users u ON sa.assigned_by = u.id
                  WHERE sa.student_id = ? AND sa.status = 'active'
                  GROUP BY s.id";
$stmt = $conn->prepare($sections_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sections = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Sections</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="my-sections.php" class="nav-link active">My Sections</a></li>
                <li><a href="view-assignments.php" class="nav-link">View Assignments</a></li>
                <li><a href="profile.php" class="nav-link">My Profile</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>My Sections</h2>
            <div class="sections-grid">
                <?php while($section = $sections->fetch_assoc()): ?>
                    <div class="section-card">
                        <h3>Section <?php echo htmlspecialchars($section['section_name']); ?></h3>
                        <p class="status">Status: <?php echo ucfirst($section['status']); ?></p>
                        
                        <h4>Courses</h4>
                        <ul class="course-list">
                            <?php foreach(explode(',', $section['courses']) as $course): ?>
                                <li><?php echo htmlspecialchars($course); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h4>Teachers</h4>
                        <ul class="teacher-list">
                            <?php foreach(explode(',', $section['teachers']) as $teacher): ?>
                                <li><?php echo htmlspecialchars($teacher); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>
