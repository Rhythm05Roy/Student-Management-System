<?php
session_start();
require_once '../db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate empty fields
        if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
            throw new Exception("All fields are required.");
        }

        // Validate role values
        $allowed_roles = ['admin', 'teacher', 'student'];
        if (!in_array($_POST['role'], $allowed_roles)) {
            throw new Exception("Invalid role selected.");
        }

        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $conn->real_escape_string($_POST['role']);

        // First, check if the users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($table_check->num_rows == 0) {
            // Create the users table if it doesn't exist
            $create_table = "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'teacher', 'student') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            if (!$conn->query($create_table)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }
        
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if (!$check_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("s", $username);
        if (!$check_stmt->execute()) {
            throw new Exception("Execute failed: " . $check_stmt->error);
        }
        
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Username already exists. Please choose another.");
        }
        $check_stmt->close();
        
        // Insert new user
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $insert_stmt->bind_param("sss", $username, $password, $role);
        if (!$insert_stmt->execute()) {
            throw new Exception("Registration failed: " . $insert_stmt->error);
        }
        
        $_SESSION['register_success'] = "Registration successful! Please login.";
        header("location: login.php");
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <form method="POST" class="register-form">
            <h2>Create Account</h2>
            <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Choose a username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Choose a password">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select your role</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn">Create Account</button>
            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </form>
    </div>
</body>
</html>
