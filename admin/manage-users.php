<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$success = $error = '';

// Handle user status update
if(isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $status = $_POST['status'] === '1' ? 'active' : 'inactive';
    
    $update_sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $user_id);
    
    if($stmt->execute()) {
        $success = "User status updated successfully";
    } else {
        $error = "Failed to update user status";
    }
}

// Get all users
$users_sql = "SELECT id, username, role, created_at, status FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="manage-users.php" class="nav-link active">Manage Users</a></li>
                <li><a href="manage-sections.php" class="nav-link">Manage Sections</a></li>
                <li><a href="../auth/logout.php" class="nav-link logout">Logout</a></li>
            </ul>
        </nav>
        <main class="dashboard-content">
            <h2>Manage Users</h2>
            <?php if($success) echo "<p class='success'>$success</p>"; ?>
            <?php if($error) echo "<p class='error'>$error</p>"; ?>

            <div class="users-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="1" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                        <input type="hidden" name="update_user" value="1">
                                    </form>
                                </td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-small">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
