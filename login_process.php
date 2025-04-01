<?php
session_start();
include 'db_connection.php';

if (!isset($_POST['email']) || !isset($_POST['password'])) {
    header("Location: login.php?error=missing_credentials");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Check user credentials in users table
$stmt = $conn->prepare("SELECT id, password, role, name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        // Set common session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['name'];
        
        // Set role-specific session variables
        if ($row['role'] === 'admin') {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_role'] = $row['role'];
            $_SESSION['admin_name'] = $row['name'];
            header("Location: admin_dashboard.php");
        } else if ($row['role'] === 'guest') {
            $_SESSION['guest_id'] = $row['id'];
            $_SESSION['guest_email'] = $email;
            $_SESSION['guest_name'] = $row['name'];
            header("Location: index.php");
        } else {
            // Handle other roles if needed
            header("Location: index.php");
        }
        exit();
    }
}

// If we get here, login failed
header("Location: login.php?error=invalid_credentials");
exit();

$conn->close();
?>