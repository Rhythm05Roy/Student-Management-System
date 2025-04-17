<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Get sections with student count
$sections_sql = "SELECT s.id, s.name, s.status,
                 COUNT(DISTINCT sa.student_id) as student_count
                 FROM sections s
                 LEFT JOIN student_assignments sa ON s.id = sa.section_id 
                 AND sa.status = 'active'
                 GROUP BY s.id
                 ORDER BY s.name";
$sections_result = $conn->query($sections_sql);

// Get courses for each section
$courses_sql = "SELECT s.name as section_name, c.course_name,
                COUNT(DISTINCT sa.student_id) as enrolled_students
                FROM sections s
                JOIN student_assignments sa ON s.id = sa.section_id
                JOIN courses c ON sa.course_id = c.id
                WHERE sa.status = 'active'
                GROUP BY s.id, c.id
                ORDER BY s.name, c.course_name";
$courses_result = $conn->query($courses_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Sections</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="students.php" class="nav-link">My Students</a></li>
                <li><a href="assign-students.php" class="nav-link">Assign Students</a></li>
                <li><a href="sections.php" class="nav-link active">View Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>Section Overview</h2>
            <div class="sections-grid">
                <?php while($section = $sections_result->fetch_assoc()): ?>
                    <div class="section-card">
                        <h3>Section <?php echo htmlspecialchars($section['name']); ?></h3>
                        <p>Total Students: <?php echo $section['student_count']; ?></p>
                        <p>Status: <?php echo ucfirst($section['status']); ?></p>
                        
                        <h4>Courses</h4>
                        <ul class="course-list">
                            <?php 
                            $courses_result->data_seek(0);
                            while($course = $courses_result->fetch_assoc()):
                                if($course['section_name'] == $section['name']):
                            ?>
                                <li>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                    (<?php echo $course['enrolled_students']; ?> students)
                                </li>
                            <?php 
                                endif;
                            endwhile;
                            ?>
                        </ul>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>
