<?php
session_start();

if (isset($_SESSION['name']) && isset($_SESSION['role'])) {
    // Show the full name instead of the user ID
    echo "Welcome, " . htmlspecialchars($_SESSION['name']) . " (" . htmlspecialchars(ucfirst($_SESSION['role'])) . ")";
} else {
    // Redirect to login if session is not set
    header("Location: login.php");
    exit();
}
?>

<!-- Booking Link -->
<a href="booking.php">Go to Booking Page</a>

<!-- Optionally, you can style the link as a button -->
<!-- <button onclick="window.location.href='booking.php'">Book a Room</button> -->

<a href="logout.php">Logout</a>
