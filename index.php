<?php
// Include the database connection
include 'db.php';

// Handle Create/Update operations
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Check if the email already exists
    $email_check_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($email_check_query);
    if ($result->num_rows > 0) {
        // If email exists, show an error message and stop insertion
        $msg = "Error: Email is already registered!";
        $alertType = "danger";
    } else {
        // Proceed to insert the new data
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing record
            $id = $_POST['id'];
            $sql = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$id";
            $msg = "User updated successfully!";
            $alertType = "success";
        } else {
            // Insert new record
            $sql = "INSERT INTO users (name, email, phone, address) VALUES ('$name', '$email', '$phone', '$address')";
            $msg = "User added successfully!";
            $alertType = "success";
        }

        if ($conn->query($sql) === TRUE) {
            // Success message
        } else {
            $msg = "Error: " . $conn->error;
            $alertType = "danger";
        }
    }
}

// Handle Edit operation (populate form with existing user data)
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    if ($result->num_rows > 0) {
        $edit = $result->fetch_assoc();
    } else {
        $msg = "User not found!";
        $alertType = "danger";
    }
}

// Handle Delete operation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($conn->query("DELETE FROM users WHERE id = $id") === TRUE) {
        // Reset auto-increment
        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");

        $msg = "User deleted successfully!";
        $alertType = "danger"; // 'danger' because we are deleting
    } else {
        $msg = "Error: " . $conn->error;
        $alertType = "danger";
    }
}

// Retrieve and display all users from the database
$sql = "SELECT id, name, email, phone, address, created_at, updated_at FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP CRUD Project</title>
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h1 class="text-center">User Management</h1>

    <!-- Display Message (success or error) -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Form for Create/Update -->
    <?php include 'form.php'; ?>

    <?php if ($result->num_rows > 0): ?>
        <h2 class="mt-5">Users List:</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row["id"] ?></td>
                            <td><?= $row["name"] ?></td>
                            <td><?= $row["email"] ?></td>
                            <td><?= $row["phone"] ?></td>
                            <td><?= $row["address"] ?></td>
                            <td><?= $row["created_at"] ?></td>
                            <td><?= $row["updated_at"] ?></td>
                            <td>
                                <a href="index.php?edit=<?= $row["id"] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="index.php?delete=<?= $row["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No records found!</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script src="script.js"></script>

<!-- Confirm Delete JavaScript -->
<script>
    // Confirm delete action
    function confirmDelete() {
        return confirm("Are you sure you want to delete this user?");
    }
</script>

</body>
</html>
