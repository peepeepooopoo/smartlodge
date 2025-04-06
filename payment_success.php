<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['payment_success'])) {
    header("Location: index.php");
    exit();
}

// Get the last booking ID from the session
$booking_id = $_SESSION['last_booking_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - SmartLodge</title>
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

        .success-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
        }

        p {
            color: #34495e;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn i {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Payment Successful!</h1>
        <p><?php echo $_SESSION['payment_success']; ?></p>
        <div class="buttons">
            <?php if ($booking_id): ?>
            <a href="generate_receipt.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">
                <i class="fas fa-download"></i> Download Receipt
            </a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Return to Home
            </a>
        </div>
    </div>
</body>
</html>
<?php
// Clear the success message after displaying
unset($_SESSION['payment_success']);
?>