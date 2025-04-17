<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$success = $error = '';

// Handle student assignment update
if(isset($_POST['update_student'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $new_section = (int)$_POST['new_section'];
    $new_teacher = (int)$_POST['new_teacher'];
    $new_course = $_POST['new_course'];
    
    $update_sql = "UPDATE student_assignments 
                   SET section_id = ?, assigned_by = ?, course_id = ? 
                   WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iiii", $new_section, $new_teacher, $new_course, $assignment_id);
    
    if($stmt->execute()) {
        $success = "Student details updated successfully";
    } else {
        $error = "Failed to update student details";
    }
}

// Handle section status update
if(isset($_POST['update_section'])) {
    $section_id = (int)$_POST['section_id'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE sections SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $section_id);
    
    if($stmt->execute()) {
        $success = "Section updated successfully";
    } else {
        $error = "Failed to update section";
    }
}

// Get all teachers
$teachers_sql = "SELECT id, username FROM users WHERE role = 'teacher' AND status = 'active'";
$teachers_result = $conn->query($teachers_sql);

// Get all courses
$courses_sql = "SELECT DISTINCT course_name FROM courses WHERE status = 'active'";
$courses_result = $conn->query($courses_sql);

// Get sections with detailed student information
$sections_sql = "SELECT 
    s.*,
    sa.id as assignment_id,
    u.username as student_name,
    t.id as teacher_id,
    t.username as teacher_name,
    c.course_name,
    (SELECT COUNT(DISTINCT sa2.student_id) 
     FROM student_assignments sa2 
     WHERE sa2.section_id = s.id AND sa2.status = 'active') as student_count
    FROM sections s
    LEFT JOIN student_assignments sa ON s.id = sa.section_id AND sa.status = 'active'
    LEFT JOIN users u ON sa.student_id = u.id
    LEFT JOIN users t ON sa.assigned_by = t.id
    LEFT JOIN courses c ON sa.course_id = c.id
    ORDER BY s.name, u.username";
$sections_result = $conn->query($sections_sql);

// Group sections data
$sections = [];
while($row = $sections_result->fetch_assoc()) {
    $section_id = $row['id'];
    if (!isset($sections[$section_id])) {
        $sections[$section_id] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'status' => $row['status'],
            'student_count' => $row['student_count'] ?? 0,
            'students' => []
        ];
    }
    if ($row['student_name']) {
        $sections[$section_id]['students'][] = [
            'assignment_id' => $row['assignment_id'],
            'student_name' => $row['student_name'],
            'teacher_id' => $row['teacher_id'],
            'teacher_name' => $row['teacher_name'],
            'course_name' => $row['course_name']
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Sections</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="manage-users.php" class="nav-link">Manage Users</a></li>
                <li><a href="manage-sections.php" class="nav-link active">Manage Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>Manage Sections</h2>
            <?php if($success) echo "<p class='success'>$success</p>"; ?>
            <?php if($error) echo "<p class='error'>$error</p>"; ?>

            <div class="sections-grid">
                <?php foreach($sections as $section): ?>
                    <div class="section-card">
                        <h3>Section <?php echo htmlspecialchars($section['name']); ?></h3>
                        <div class="section-stats">
                            <p>Total Students: <?php echo $section['student_count']; ?></p>
                        </div>
                        
                        <!-- Section Status Form -->
                        <form method="POST" class="section-form">
                            <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                            <div class="form-group">
                                <label>Section Status:</label>
                                <select name="status" class="status-select">
                                    <option value="open" <?php echo $section['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="closed" <?php echo $section['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                                <button type="submit" name="update_section" class="btn-small">Update Status</button>
                            </div>
                        </form>

                        <?php if(!empty($section['students'])): ?>
                            <div class="students-list">
                                <h4>Enrolled Students</h4>
                                <table class="data-table">
                                    <tr>
                                        <th>Student</th>
                                        <th>Teacher</th>
                                        <th>Course</th>
                                        <th>Section</th>
                                        <th>Actions</th>
                                    </tr>
                                    <?php foreach($section['students'] as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                            <form method="POST" class="inline-form">
                                                <input type="hidden" name="assignment_id" value="<?php echo $student['assignment_id']; ?>">
                                                <td>
                                                    <select name="new_teacher" class="small-select">
                                                        <?php 
                                                        $teachers_result->data_seek(0);
                                                        while($teacher = $teachers_result->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $teacher['id']; ?>" 
                                                                <?php echo $student['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($teacher['username']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="new_course" class="small-select">
                                                        <?php 
                                                        $courses_result->data_seek(0);
                                                        while($course = $courses_result->fetch_assoc()): 
                                                        ?>
                                                            <option value="<?php echo $course['course_name']; ?>"
                                                                <?php echo $student['course_name'] === $course['course_name'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($course['course_name']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="new_section" class="small-select">
                                                        <?php foreach($sections as $other_section): ?>
                                                            <option value="<?php echo $other_section['id']; ?>"
                                                                <?php echo $section['id'] === $other_section['id'] ? 'selected' : ''; ?>>
                                                                Section <?php echo htmlspecialchars($other_section['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <button type="submit" name="update_student" class="btn-small">Update</button>
                                                </td>
                                            </form>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
