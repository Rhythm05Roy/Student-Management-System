<?php
// db.php - Database connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simple_project_db"; // Replace with your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
