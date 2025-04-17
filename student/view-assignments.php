<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get assignments for student's sections
$assignments_query = "SELECT a.*, s.name as section_name, c.course_name, u.username as teacher_name
                     FROM assignments a
                     JOIN sections s ON a.section_id = s.id
                     JOIN courses c ON a.course_id = c.id
                     JOIN users u ON a.teacher_id = u.id
                     JOIN student_assignments sa ON s.id = sa.section_id
                     WHERE sa.student_id = ? 
                     AND sa.status = 'active'
                     AND a.status = 'active'
                     ORDER BY a.due_date ASC";
$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Assignments</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="my-sections.php" class="nav-link">My Sections</a></li>
                <li><a href="view-assignments.php" class="nav-link active">View Assignments</a></li>
                <li><a href="profile.php" class="nav-link">My Profile</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>My Assignments</h2>
            <div class="assignments-grid">
                <?php if ($assignments->num_rows > 0): ?>
                    <?php while($assignment = $assignments->fetch_assoc()): ?>
                        <div class="assignment-card">
                            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                            <p class="course-info">
                                <?php echo htmlspecialchars($assignment['course_name']); ?> - 
                                Section <?php echo htmlspecialchars($assignment['section_name']); ?>
                            </p>
                            <div class="description">
                                <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                            </div>
                            <div class="assignment-meta">
                                <p><strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($assignment['due_date'])); ?></p>
                                <p><strong>Teacher:</strong> <?php echo htmlspecialchars($assignment['teacher_name']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No assignments available.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
