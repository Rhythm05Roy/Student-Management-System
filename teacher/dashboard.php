<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get count of assigned students
$student_query = "SELECT COUNT(DISTINCT student_id) as student_count 
                 FROM student_assignments 
                 WHERE assigned_by = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_result = $stmt->get_result()->fetch_assoc();
$student_count = $student_result['student_count'];

// Get count of assignments
$assignment_query = "SELECT COUNT(*) as assignment_count 
                    FROM assignments 
                    WHERE teacher_id = ? AND status = 'active'";
$stmt = $conn->prepare($assignment_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assignment_result = $stmt->get_result()->fetch_assoc();
$assignment_count = $assignment_result['assignment_count'];

// Get count of sections with active assignments by this teacher
$section_query = "SELECT COUNT(DISTINCT s.id) as section_count 
                 FROM sections s
                 INNER JOIN student_assignments sa ON s.id = sa.section_id
                 INNER JOIN courses c ON sa.course_id = c.id
                 WHERE sa.assigned_by = ? 
                 AND sa.status = 'active'
                 AND c.teacher_id = ?
                 AND s.status = 'open'";
$stmt = $conn->prepare($section_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$section_result = $stmt->get_result()->fetch_assoc();
$section_count = $section_result['section_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="students.php" class="nav-link">My Students</a></li>
                <li><a href="assign-students.php" class="nav-link">Assign Students</a></li>
                <li><a href="create-assignment.php" class="nav-link">Create Assignment</a></li>
                <li><a href="sections.php" class="nav-link">View Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h1>Welcome, Teacher</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>My Students</h3>
                    <p class="count"><?php echo $student_count; ?></p>
                    <a href="students.php" class="card-link">View All Students</a>
                </div>
                <div class="card">
                    <h3>Active Sections</h3>
                    <p class="count"><?php echo $section_count; ?></p>
                    <a href="sections.php" class="card-link">Manage Sections</a>
                </div>
                <div class="card">
                    <h3>Assignments</h3>
                    <p class="count"><?php echo $assignment_count; ?></p>
                    <a href="create-assignment.php" class="card-link">Create New Assignment</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
