<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in to view your bookings.");
}

$guest_id = $_SESSION['user_id'];

$query = "SELECT bookings.*, rooms.room_number FROM bookings 
          JOIN rooms ON bookings.room_id = rooms.id 
          WHERE guest_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
</head>
<body>
    <h2>My Bookings</h2>

    <table border="1">
        <tr>
            <th>Room Number</th>
            <th>Check-in Date</th>
            <th>Check-out Date</th>
            <th>Status</th>
        </tr>
        <?php while ($booking = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $booking['room_number']; ?></td>
                <td><?php echo $booking['check_in_date']; ?></td>
                <td><?php echo $booking['check_out_date']; ?></td>
                <td><?php echo $booking['status']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
