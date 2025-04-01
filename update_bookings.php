<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET Status = ? WHERE BookingID = ?");
    $stmt->bind_param("si", $status, $bookingId);
    
    if ($stmt->execute()) {
        echo "Booking #$bookingId has been $status.";
    } else {
        echo "Error updating booking: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>