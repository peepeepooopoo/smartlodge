<?php
session_start();
require 'db_connection.php';

if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided");
}

$booking_id = $_GET['booking_id'];

// Get booking and payment details
$query = "SELECT b.*, g.FullName, g.Email, g.Phone, r.RoomNumber, rt.Name as RoomType, 
          p.PaymentDate, p.PaymentMethod, p.Amount as PaymentAmount
          FROM booking b
          JOIN guest g ON b.GuestID = g.GuestID
          JOIN rooms r ON b.RoomNumber = r.RoomNumber
          JOIN roomtype rt ON r.TypeID = rt.TypeID
          JOIN payment p ON b.BookingID = p.BookingID
          WHERE b.BookingID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - <?php echo $booking_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .hotel-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .hotel-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            width: 150px;
        }
        .value {
            flex: 1;
        }
        .thank-you {
            text-align: center;
            margin-top: 30px;
            font-style: italic;
            color: #666;
        }
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        @media print {
            .print-button {
                display: none;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <img src="images/logo.png" alt="Hotel Logo" class="hotel-logo">
            <div class="hotel-name">SmartLodge Hotel</div>
            <div>123 Hotel Street, City, Country</div>
            <div>Phone: +1234567890 | Email: info@smartlodge.com</div>
        </div>
        
        <div class="receipt-title">BOOKING RECEIPT</div>
        
        <div class="section">
            <div class="section-title">Booking Information</div>
            <div class="info-row">
                <div class="label">Receipt No:</div>
                <div class="value"><?php echo $booking_id; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Date:</div>
                <div class="value"><?php echo date('d/m/Y', strtotime($booking['PaymentDate'])); ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Guest Information</div>
            <div class="info-row">
                <div class="label">Name:</div>
                <div class="value"><?php echo $booking['FullName']; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Email:</div>
                <div class="value"><?php echo $booking['Email']; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Phone:</div>
                <div class="value"><?php echo $booking['Phone']; ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Booking Details</div>
            <div class="info-row">
                <div class="label">Room Type:</div>
                <div class="value"><?php echo $booking['RoomType']; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Room Number:</div>
                <div class="value"><?php echo $booking['RoomNumber']; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Check-in:</div>
                <div class="value"><?php echo date('d/m/Y', strtotime($booking['CheckInDate'])); ?></div>
            </div>
            <div class="info-row">
                <div class="label">Check-out:</div>
                <div class="value"><?php echo date('d/m/Y', strtotime($booking['CheckOutDate'])); ?></div>
            </div>
            <div class="info-row">
                <div class="label">Guests:</div>
                <div class="value"><?php echo $booking['Capacity']; ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Payment Details</div>
            <div class="info-row">
                <div class="label">Payment Method:</div>
                <div class="value"><?php echo $booking['PaymentMethod']; ?></div>
            </div>
            <div class="info-row">
                <div class="label">Amount Paid:</div>
                <div class="value">₹<?php echo number_format($booking['PaymentAmount'], 2); ?></div>
            </div>
        </div>
        
        <div class="thank-you">
            <p>Thank you for choosing SmartLodge Hotel!</p>
            <p>We hope you enjoy your stay with us.</p>
        </div>
        
        <button class="print-button" onclick="window.print()">Print Receipt</button>
    </div>
    
    <script>
        // Automatically trigger print dialog when page loads
        window.onload = function() {
            // Uncomment the line below if you want the print dialog to open automatically
            // window.print();
        };
    </script>
</body>
</html> 