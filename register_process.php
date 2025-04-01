<?php
session_start();
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];
    $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $dob = $_POST['dob'];
    $country = htmlspecialchars($_POST['country'], ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email format";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = "Email already registered";
        header("Location: register.php");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, country) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $country);
        $stmt->execute();
        $user_id = $conn->insert_id;

        // If role is guest, insert into guest table
        if ($role === 'guest') {
            $stmt = $conn->prepare("INSERT INTO guest (GuestID, FullName, DateOfBirth, Address, Phone, Email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $user_id, $name, $dob, $address, $phone, $email);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Set success message
        $_SESSION['register_success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['register_error'] = "Registration failed: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
} else {
    // If not POST request, redirect to registration page
    header("Location: register.php");
    exit();
}
?>
