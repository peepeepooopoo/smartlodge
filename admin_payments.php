<!-- admin_payments.php -->
<?php
session_start();
include 'db_connection.php';

// Only allow admins to view payment history
if ($_SESSION['role'] != 'admin') {
    echo "Access denied.";
    exit;
}

$result = $conn->query("SELECT * FROM payment");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
</head>
<body>
    <h2>Payment History</h2>
    <table border="1">
        <tr>
            <th>Payment ID</th>
            <th>Booking ID</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Payment Method</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['PaymentID']; ?></td>
            <td><?php echo $row['BookingID']; ?></td>
            <td><?php echo $row['Amount']; ?></td>
            <td><?php echo $row['PaymentDate']; ?></td>
            <td><?php echo $row['PaymentMethod']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
