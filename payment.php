<?php
session_start();
include 'db_connection.php'; 

if (!isset($_GET['booking_id']) || !isset($_GET['amount'])) {
    die("Error: Missing booking details.");
}

$booking_id = $_GET['booking_id'];
$amount = $_GET['amount'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .payment-body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .payment-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .booking-details {
            margin-bottom: 1.5rem;
            background-color: #f0f0f0;
            padding: 1rem;
            border-radius: 5px;
            font-size: 16px;
        }

        .booking-details p {
            margin: 0.5rem 0;
        }

        label {
            display: block;
            font-size: 14px;
            margin-bottom: 0.5rem;
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .links-container {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .direct-link {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
        }

        .direct-link:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .payment-container {
                width: 90%;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="payment-body">
    <div class="payment-container">
        <h1>Complete Your Payment</h1>

        <!-- Display Booking ID and Total Amount -->
        <div class="booking-details">
            <p><strong>Booking ID:</strong> <?php echo $booking_id; ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($amount, 2); ?></p>
        </div>

        <!-- Payment Form -->
        <form action="payment_process.php" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            
            <label for="payment_method">Choose Payment Method:</label>
            <select id="payment_method" name="payment_method" required>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
            </select>

            <div class="payment-details">
                <!-- Credit/Debit Card Fields -->
                <div id="card-fields">
                    <label for="card-number">Card Number:</label>
                    <input type="text" id="card-number" name="card-number" pattern="\d{16}" placeholder="1234 5678 9012 3456" required>

                    <label for="card-expiry">Expiry Date:</label>
                    <input type="text" id="card-expiry" name="card-expiry" pattern="\d{2}/\d{2}" placeholder="MM/YY" required>

                    <label for="card-cvv">CVV:</label>
                    <input type="text" id="card-cvv" name="card-cvv" pattern="\d{3}" placeholder="123" required>
                </div>

                <!-- PayPal Field -->
                <div id="paypal-email" style="display: none;">
                    <label for="paypal-email-input">PayPal Email:</label>
                    <input type="email" id="paypal-email-input" name="paypal-email" placeholder="you@example.com">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit">Pay Now</button>
        </form>
        
        <!-- Links Container -->
        <div class="links-container">
            <a href="index.php" class="direct-link">← Back to Home</a>
    </div>

    <!-- Script to toggle payment fields -->
    <script>
        const paymentMethod = document.getElementById('payment_method');
        const cardFields = document.getElementById('card-fields');
        const paypalEmail = document.getElementById('paypal-email');

        paymentMethod.addEventListener('change', function() {
            if (this.value === 'paypal') {
                cardFields.style.display = 'none';
                paypalEmail.style.display = 'block';
            } else {
                cardFields.style.display = 'block';
                paypalEmail.style.display = 'none';
            }
        });
    </script>
</body>
</html>