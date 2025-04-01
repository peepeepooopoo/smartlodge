<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Error: Unauthorized access.");
}

$bookingID = $_GET['id'];

$stmt = $conn->prepare("UPDATE booking SET status = 'cancelled' WHERE BookingID = ?");
$stmt->bind_param("i", $bookingID);

if ($stmt->execute()) {
    echo "Booking cancelled successfully!";
} else {
    echo "Error: Could not cancel the booking.";
}

$stmt->close();
$conn->close();
?>