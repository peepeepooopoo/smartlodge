<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT id, name, password FROM guests WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['guest_id'] = $row['id'];
            $_SESSION['guest_name'] = $row['name'];

            header("Location: guest_dashboard.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Guest not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Guest Login</title>
</head>
<body>
    <h2>Guest Login</h2>
    <form method="POST">
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
