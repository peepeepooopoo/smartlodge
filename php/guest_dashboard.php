<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guest') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?> (Guest)</h2>
    <a href="logout.php">Logout</a>
</body>
</html>
