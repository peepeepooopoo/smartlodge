<?php
include 'db_connection.php'; // Ensure the database connection is included

// Fetch available rooms
$roomQuery = "SELECT RoomNumber, TypeID FROM rooms WHERE Status = 'available'";
$result = $conn->query($roomQuery);
?>

<select name="room_number" id="room_number">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <option value="<?= $row['RoomNumber']; ?>">Room <?= $row['RoomNumber']; ?></option>
    <?php } ?>
</select>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Room</title>
</head>
<body>
    <h2>Book a Room</h2>

    <form action="booking_process.php" method="POST">
    <label for="room_type">Choose a Room Type:</label>
    <select name="room_type" id="room_type">
        <option value="1">Standard</option>
        <option value="2">Deluxe</option>
    </select>

    <label for="room_number">Choose a Room:</label>
    <select name="room_number" id="room_number">
        <!-- This should be dynamically populated from the database -->
    </select>

    <label for="checkin_date">Check-in Date:</label>
    <input type="date" name="checkin_date" required>

    <label for="checkout_date">Check-out Date:</label>
    <input type="date" name="checkout_date" required>

    <button type="submit">Book Now</button>
</form>

</body>
</html>
