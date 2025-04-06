<?php
session_start();
include 'db_connection.php';
include 'auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate role
    if (!in_array($role, ['admin', 'guest'])) {
        $_SESSION['login_error'] = "Invalid role selected";
        header("Location: login.php");
        exit();
    }

    // Query the users table for authentication
    $query = "SELECT u.id, u.email, u.password, u.name, u.role, g.GuestID 
              FROM users u 
              LEFT JOIN guest g ON u.id = g.GuestID 
              WHERE u.email = ? AND u.role = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Clear any existing sessions
            session_unset();
            
            // Set common session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            
            // Set role-specific session variables
            if ($role === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['is_admin'] = true;
                
                header("Location: admin_dashboard.php");
            } else {
                // For guests, ensure we have a GuestID
                if (!isset($user['GuestID']) || $user['GuestID'] === null) {
                    // If no GuestID exists, create a guest record
                    $guest_stmt = $conn->prepare("INSERT INTO guest (GuestID, FullName, Email) VALUES (?, ?, ?)");
                    $guest_stmt->bind_param("iss", $user['id'], $user['name'], $user['email']);
                    $guest_stmt->execute();
                    $user['GuestID'] = $user['id']; // Use the same ID as the user
                }
                
                $_SESSION['guest_id'] = $user['GuestID'];
                $_SESSION['guest_user_id'] = $user['id'];
                $_SESSION['guest_email'] = $user['email'];
                $_SESSION['guest_name'] = $user['name'];
                $_SESSION['is_guest'] = true;
                
                header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password";
    }

    $stmt->close();
    header("Location: login.php");
    exit();
}

// If not POST request, redirect to login page
header("Location: login.php");
exit();
?>