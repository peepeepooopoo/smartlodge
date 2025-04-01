<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $amount = $_POST['amount'];
    $payment_method = trim($_POST['payment_method']);

    // For debugging (optional) - comment out or remove in production
    // echo "Submitted payment method: " . htmlspecialchars($payment_method);
    // echo "Booking ID: " . htmlspecialchars($booking_id);
    // echo "Amount: " . htmlspecialchars($amount);

    // Validate payment method
    $valid_methods = ['Credit Card', 'Debit Card', 'Cash', 'UPI'];
    if (!in_array($payment_method, $valid_methods)) {
        die("Invalid payment method selected.");
    }

    $payment_date = date("Y-m-d"); // Use today's date for payment

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into the payment table
        $query = "INSERT INTO payment (BookingID, Amount, PaymentDate, PaymentMethod)
                  VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("idss", $booking_id, $amount, $payment_date, $payment_method);
        $stmt->execute();
        $stmt->close();

        // Update booking status to approved
        $update_query = "UPDATE booking SET Status = 'approved' WHERE BookingID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to success page
        header("Location: payment_success.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>