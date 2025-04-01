<?php
session_start();
// You can retrieve payment details from session if needed
// $booking_id = $_SESSION['booking_id'] ?? 'Unknown';
// $amount = $_SESSION['amount'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .confirmation-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            padding: 20px;
        }

        .confirmation-card {
            background-color: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem;
        }

        .success-icon::before {
            content: "✓";
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 1rem;
            color: #28a745;
        }

        p {
            margin-bottom: 1.5rem;
            font-size: 16px;
            line-height: 1.6;
        }

        .confirmation-details {
            background-color: #f0f0f0;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .confirmation-details p {
            margin: 0.5rem 0;
        }

        .reference-number {
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 1px;
            background-color: #f8f8f8;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px dashed #ccc;
            display: inline-block;
        }

        .home-button {
            padding: 0.75rem 2rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
        }

        .home-button:hover {
            background-color: #0069d9;
        }

        .receipt-link {
            display: block;
            margin-top: 1.5rem;
            color: #007bff;
            text-decoration: none;
        }

        .receipt-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon"></div>
            <h1>Payment Successful!</h1>
            <p>Your payment has been processed successfully. Thank you for your booking.</p>
            
            <div class="confirmation-details">
                <p><strong>Transaction ID:</strong> <?php echo 'TXN-' . rand(100000, 999999); ?></p>
                <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a"); ?></p>
                <?php if (isset($_SESSION['booking_id'])): ?>
                <p><strong>Booking ID:</strong> <?php echo $_SESSION['booking_id']; ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['amount'])): ?>
                <p><strong>Amount Paid:</strong> $<?php echo number_format($_SESSION['amount'], 2); ?></p>
                <?php endif; ?>
            </div>
            
            <p>A confirmation email has been sent to your registered email address.</p>
            <p>Your reference number is:</p>
            <div class="reference-number"><?php echo 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8)); ?></div>
            
            <a href="index.php" class="home-button">Return to Home</a>
            <a href="download_receipt.php<?php echo isset($_SESSION['booking_id']) ? '?booking_id='.$_SESSION['booking_id'] : ''; ?>" class="receipt-link">Download Receipt</a>
        </div>
    </div>
</body>
</html>