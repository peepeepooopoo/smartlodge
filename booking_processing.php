<?php
session_start();
include 'db_connection.php';

// Debug session variables
error_log("Session variables: " . print_r($_SESSION, true));

if (!isset($_SESSION['guest_id']) || !isset($_GET['booking_id'])) {
    error_log("Missing required session variables or booking_id");
    header("Location: index.php");
    exit();
}

$bookingId = $_GET['booking_id'];
$guestId = $_SESSION['guest_id'];

// Debug query parameters
error_log("Query parameters - BookingID: $bookingId, GuestID: $guestId");

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, g.FullName, rt.Name as room_type, rt.PricePerNight 
    FROM booking b
    JOIN guest g ON b.GuestID = g.GuestID
    JOIN rooms r ON b.RoomNumber = r.RoomNumber
    JOIN roomtype rt ON r.TypeID = rt.TypeID
    WHERE b.BookingID = ? AND b.GuestID = ?
");
$stmt->bind_param("ii", $bookingId, $guestId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

// Debug query result
error_log("Query result: " . ($booking ? "Found booking" : "No booking found"));

if (!$booking) {
    // Log the error for debugging
    error_log("Booking not found - BookingID: $bookingId, GuestID: $guestId");
    
    // Let's check if the booking exists at all
    $check_stmt = $conn->prepare("SELECT * FROM booking WHERE BookingID = ?");
    $check_stmt->bind_param("i", $bookingId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $booking_exists = $check_result->fetch_assoc();
    error_log("Booking exists check: " . ($booking_exists ? "Yes" : "No"));
    
    // Check if the guest exists
    $guest_check = $conn->prepare("SELECT * FROM guest WHERE GuestID = ?");
    $guest_check->bind_param("i", $guestId);
    $guest_check->execute();
    $guest_result = $guest_check->get_result();
    $guest_exists = $guest_result->fetch_assoc();
    error_log("Guest exists check: " . ($guest_exists ? "Yes" : "No"));
    
    $_SESSION['error'] = "Booking details not found. Please contact support if this persists.";
    header("Location: index.php");
    exit();
}

// If booking is approved, redirect to payment
if ($booking['Status'] == 'approved') {
    header("Location: payment.php?booking_id=" . $bookingId . "&amount=" . $booking['TotalPrice']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Processing - SmartLodge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/register1.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .processing-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .processing-icon {
            font-size: 48px;
            color: #f39c12;
            margin-bottom: 20px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }

        .booking-details p {
            margin: 10px 0;
            color: #2c3e50;
        }

        .booking-details strong {
            color: #34495e;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .status-pending {
            background: #f1c40f;
            color: #fff;
        }

        .status-approved {
            background: #2ecc71;
            color: #fff;
        }

        .status-rejected {
            background: #e74c3c;
            color: #fff;
        }

        .refresh-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .refresh-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="processing-container">
        <i class="fas fa-spinner processing-icon"></i>
        <h1>Booking Processing</h1>
        
        <div class="booking-details">
            <p><strong>Booking ID:</strong> #<?php echo $booking['BookingID']; ?></p>
            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($booking['FullName']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo date('F j, Y', strtotime($booking['CheckInDate'])); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo date('F j, Y', strtotime($booking['CheckOutDate'])); ?></p>
            <p><strong>Total Price:</strong> $<?php echo number_format($booking['TotalPrice'], 2); ?></p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-<?php echo strtolower($booking['Status']); ?>">
                    <?php echo ucfirst($booking['Status']); ?>
                </span>
            </p>
        </div>

        <p>Your booking is being processed. Please wait while we confirm your reservation.</p>
        <p>The page will automatically refresh every 30 seconds to check the status.</p>
        
        <button class="refresh-button" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh Now
        </button>
    </div>

    <script>
        // Auto-refresh the page every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html> 