<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in to book a room.");
}

$guest_id = $_SESSION['user_id'];
$room_number = $_POST['room_number'];
$check_in = $_POST['check_in_date'];
$check_out = $_POST['check_out_date'];
$status = "pending";

// Insert booking into database
$stmt = $conn->prepare("INSERT INTO bookings (guest_id, room_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $guest_id, $room_number, $check_in, $check_out, $status);

if ($stmt->execute()) {
    echo "Booking request submitted!";
} else {
    echo "Error: " . $stmt->error;
}
?>
