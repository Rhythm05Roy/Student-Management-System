<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Handle assignment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $section_id = (int)$_POST['section_id'];
    $course_id = (int)$_POST['course_id'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO assignments (title, description, section_id, course_id, teacher_id, due_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiss", $title, $description, $section_id, $course_id, $user_id, $due_date);
    
    if ($stmt->execute()) {
        $success = "Assignment created successfully!";
    } else {
        $error = "Error creating assignment: " . $conn->error;
    }
}

// Get sections
$sections_sql = "SELECT id, name FROM sections WHERE status = 'open'";
$sections_result = $conn->query($sections_sql);

// Get courses
$courses_sql = "SELECT id, course_name FROM courses WHERE teacher_id = ? AND status = 'active'";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Get existing assignments
$assignments_sql = "SELECT a.*, s.name as section_name, c.course_name 
                   FROM assignments a
                   JOIN sections s ON a.section_id = s.id
                   JOIN courses c ON a.course_id = c.id
                   WHERE a.teacher_id = ? AND a.status = 'active'
                   ORDER BY a.created_at DESC";
$assignments_stmt = $conn->prepare($assignments_sql);
$assignments_stmt->bind_param("i", $user_id);
$assignments_stmt->execute();
$assignments_result = $assignments_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Assignment</title>
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
                <li><a href="create-assignment.php" class="nav-link active">Create Assignment</a></li>
                <li><a href="sections.php" class="nav-link">View Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <div class="assignment-container">
                <!-- Create Assignment Form -->
                <div class="create-assignment">
                    <h2>Create New Assignment</h2>
                    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
                    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

                    <form method="POST" class="assignment-form">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" required placeholder="Enter assignment title">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" required rows="4" 
                                    placeholder="Enter assignment description"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Section</label>
                            <select name="section_id" required>
                                <option value="">Select Section</option>
                                <?php while($section = $sections_result->fetch_assoc()): ?>
                                    <option value="<?php echo $section['id']; ?>">
                                        Section <?php echo htmlspecialchars($section['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Course</label>
                            <select name="course_id" required>
                                <option value="">Select Course</option>
                                <?php while($course = $courses_result->fetch_assoc()): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" name="due_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <button type="submit" class="btn">Create Assignment</button>
                    </form>
                </div>

                <!-- View Existing Assignments -->
                <div class="view-assignments">
                    <h2>Current Assignments</h2>
                    <div class="assignments-grid">
                        <?php if ($assignments_result->num_rows > 0): ?>
                            <?php while($assignment = $assignments_result->fetch_assoc()): ?>
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
                                        <p><strong>Due Date:</strong> 
                                            <?php echo date('F j, Y', strtotime($assignment['due_date'])); ?>
                                        </p>
                                        <p><strong>Created:</strong> 
                                            <?php echo date('F j, Y', strtotime($assignment['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No assignments created yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
