<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Handle remove student
if (isset($_POST['unassign_id'])) {
    $unassign_id = (int)$_POST['unassign_id'];
    
    $unassign_sql = "UPDATE student_assignments 
                     SET status = 'inactive' 
                     WHERE id = ? AND assigned_by = ?";
    $unassign_stmt = $conn->prepare($unassign_sql);
    $unassign_stmt->bind_param("ii", $unassign_id, $user_id);
    
    if ($unassign_stmt->execute()) {
        $success = "Student successfully removed!";
    } else {
        $error = "Error removing student: " . $conn->error;
    }
}

// Handle update assignment
if (isset($_POST['update_assignment'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $new_section = $_POST['new_section'];
    $new_course = $_POST['new_course'];
    
    $update_sql = "UPDATE student_assignments 
                   SET section_id = ?, course_id = ? 
                   WHERE id = ? AND assigned_by = ? AND status = 'active'";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iiii", $new_section, $new_course, $assignment_id, $user_id);
    
    if ($update_stmt->execute()) {
        $success = "Assignment updated successfully!";
    } else {
        $error = "Error updating assignment: " . $conn->error;
    }
}

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['unassign_id']) && !isset($_POST['update_assignment'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_name'];
    $section_id = $_POST['section'];
    
    // Check if student is already assigned
    $check_sql = "SELECT id FROM student_assignments 
                  WHERE student_id = ? AND section_id = ? AND course_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $student_id, $section_id, $course_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "This student is already assigned to this section and course!";
    } else {
        $sql = "INSERT INTO student_assignments (student_id, section_id, course_id, assigned_by, status) 
                VALUES (?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $student_id, $section_id, $course_id, $user_id);
        
        if ($stmt->execute()) {
            $success = "Student successfully assigned!";
        } else {
            $error = "Error assigning student: " . $conn->error;
        }
    }
}

// Get unassigned students
$students_sql = "SELECT id, username FROM users WHERE role = 'student'";
$students_result = $conn->query($students_sql);

// Get sections
$sections_sql = "SELECT id, name FROM sections";
$sections_result = $conn->query($sections_sql);

// Get courses (modified query)
$courses_sql = "INSERT INTO courses (course_name, teacher_id, status) 
                SELECT t.course_name, ?, 'active'
                FROM (
                    SELECT 'CSE1' as course_name
                    UNION SELECT 'CSE2'
                    UNION SELECT 'CSE3'
                    UNION SELECT 'CSE4'
                    UNION SELECT 'CSE5'
                ) t
                WHERE NOT EXISTS (
                    SELECT 1 FROM courses 
                    WHERE teacher_id = ? AND course_name = t.course_name
                )";
$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();

// Now get all courses for this teacher
$get_courses_sql = "SELECT id, course_name FROM courses WHERE teacher_id = ? AND status = 'active'";
$courses_stmt = $conn->prepare($get_courses_sql);
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Get currently assigned students
$assigned_sql = "SELECT sa.id, u.username, u.id as student_id, 
                        c.course_name, c.id as course_id,
                        s.name as section_name, s.id as section_id
                 FROM student_assignments sa 
                 JOIN users u ON sa.student_id = u.id 
                 JOIN courses c ON sa.course_id = c.id 
                 JOIN sections s ON sa.section_id = s.id
                 WHERE sa.assigned_by = ? AND sa.status = 'active'";
$assigned_stmt = $conn->prepare($assigned_sql);
$assigned_stmt->bind_param("i", $user_id);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Students</title>
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
                <li><a href="assign-students.php" class="nav-link active">Assign Students</a></li>
                <li><a href="sections.php" class="nav-link">View Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>Assign Students to Sections and Courses</h2>
            <?php if ($success) echo "<p class='success'>$success</p>"; ?>
            <?php if ($error) echo "<p class='error'>$error</p>"; ?>
            
            <div class="assignment-container">
                <form method="POST" class="assignment-form">
                    <div class="form-group">
                        <label>Select Student:</label>
                        <select name="student_id" required>
                            <option value="">Choose a student</option>
                            <?php while($student = $students_result->fetch_assoc()): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Section:</label>
                        <select name="section" required>
                            <option value="">Choose a section</option>
                            <?php while($section = $sections_result->fetch_assoc()): ?>
                                <option value="<?php echo $section['id']; ?>">
                                    Section <?php echo htmlspecialchars($section['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Course:</label>
                        <select name="course_name" required>
                            <option value="">Choose a course</option>
                            <?php 
                            if ($courses_result->num_rows > 0) {
                                while($course = $courses_result->fetch_assoc()): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endwhile;
                            } else {
                                echo "<option disabled>No courses available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Assign Student</button>
                </form>

                <div class="assigned-students">
                    <h3>Currently Assigned Students</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Section</th>
                                <th>Course</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($assigned = $assigned_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assigned['username']); ?></td>
                                    <td>
                                        <form method="POST" class="inline-form" id="update_<?php echo $assigned['id']; ?>">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assigned['id']; ?>">
                                            <select name="new_section" onchange="this.form.submit()">
                                                <?php 
                                                $sections_result->data_seek(0);
                                                while($section = $sections_result->fetch_assoc()): 
                                                    $selected = ($section['id'] == $assigned['section_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $section['id']; ?>" <?php echo $selected; ?>>
                                                        Section <?php echo htmlspecialchars($section['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <input type="hidden" name="update_assignment" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assigned['id']; ?>">
                                            <select name="new_course" onchange="this.form.submit()">
                                                <?php 
                                                $courses_result->data_seek(0);
                                                while($course = $courses_result->fetch_assoc()): 
                                                    $selected = ($course['id'] == $assigned['course_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $course['id']; ?>" <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <input type="hidden" name="update_assignment" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="unassign_id" value="<?php echo $assigned['id']; ?>">
                                            <button type="submit" class="btn-small btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
