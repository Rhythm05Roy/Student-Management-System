<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Get student details
$user_query = "SELECT username, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        // Verify current password
        $verify_query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (password_verify($current_password, $result['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    } else {
        $error = "New passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
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
                <li><a href="view-assignments.php" class="nav-link">View Assignments</a></li>
                <li><a href="profile.php" class="nav-link active">My Profile</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>My Profile</h2>
            <?php if ($success) echo "<p class='success'>$success</p>"; ?>
            <?php if ($error) echo "<p class='error'>$error</p>"; ?>
            
            <div class="profile-card">
                <div class="profile-info">
                    <h3>Account Information</h3>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="password-update">
                    <h3>Change Password</h3>
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_password" class="btn">Update Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
