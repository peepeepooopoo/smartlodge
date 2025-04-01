<?php
session_start();
include 'db_connection.php';

// Check if 'id' is present in the URL
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Prepare and execute the SQL query to approve the booking
    $stmt = $conn->prepare("UPDATE booking SET status = 'confirmed' WHERE BookingID = ?");
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        echo "Booking approved successfully!";
    } else {
        echo "Error approving booking: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Error: Booking ID not found.";
}

$conn->close();
?>
