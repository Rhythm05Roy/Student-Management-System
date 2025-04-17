<?php
session_start();
require_once '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                switch($user['role']) {
                    case 'admin':
                        header("location: ../admin/dashboard.php");
                        break;
                    case 'teacher':
                        header("location: ../teacher/dashboard.php");
                        break;
                    case 'student':
                        header("location: ../student/dashboard.php");
                        break;
                }
                exit;
            }
        }
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <form method="POST" class="login-form">
            <h2>Welcome Back</h2>
            <?php 
            if(isset($_SESSION['register_success'])) {
                echo "<p class='success'>" . $_SESSION['register_success'] . "</p>";
                unset($_SESSION['register_success']);
            }
            if(isset($error)) echo "<p class='error'>$error</p>"; 
            ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="btn">Sign In</button>
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>
</body>
</html>
