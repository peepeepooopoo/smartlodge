<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role = $_POST['role']; // Get user role from form

    if ($role == "guest") {
        // Insert into guests table
        $stmt = $conn->prepare("INSERT INTO guests (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
    } else {
        // Insert into users table (for Admin/Staff)
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='index.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

    <h2>Register</h2>
    <form action="register_process.php" method="post">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <!-- Role Selection -->
        <label for="role">Register as:</label>
        <select id="role" name="role">
            <option value="guest">Guest</option>
            <option value="admin">Admin/Staff</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>

