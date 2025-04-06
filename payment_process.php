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

    // Check if booking exists and is approved
    $check_booking = $conn->prepare("SELECT Status FROM booking WHERE BookingID = ?");
    $check_booking->bind_param("i", $booking_id);
    $check_booking->execute();
    $booking_result = $check_booking->get_result();
    $booking = $booking_result->fetch_assoc();
    $check_booking->close();

    if (!$booking) {
        die("Booking not found.");
    }

    if ($booking['Status'] !== 'approved') {
        die("This booking is not yet approved for payment.");
    }

    $payment_date = date("Y-m-d");

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

        // Update booking status to completed
        $update_query = "UPDATE booking SET Status = 'completed' WHERE BookingID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Commit transaction
        $conn->commit();

        // Store booking ID in session for receipt download
        $_SESSION['last_booking_id'] = $booking_id;

        // Redirect to success page
        $_SESSION['payment_success'] = "Payment processed successfully! Your booking is now confirmed.";
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