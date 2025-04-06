<?php
session_start();
require 'db_connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug logging
error_log("Booking process started");

// Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['booking_error'] = "Invalid request method";
    header("Location: booking.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Validate and extract form data
    $required = ['room_type', 'checkin_date', 'checkout_date', 'guests'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $room_type = (int)$_POST['room_type'];
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    $guests = (int)$_POST['guests'];

    error_log("Form data received - Room Type: $room_type, Check-in: $checkin_date, Check-out: $checkout_date, Guests: $guests");

    // 2. Validate session
    if (!isset($_SESSION['guest_id'])) {
        $_SESSION['booking_error'] = "You must be logged in as a guest to book a room.";
        header("Location: booking.php");
        exit();
    }
    $guest_id = $_SESSION['guest_id'];
    error_log("Guest ID: $guest_id");

    // 3. Find an available room of the selected type
    $room_query = "SELECT r.RoomNumber, rt.PricePerNight, rt.Capacity 
                  FROM rooms r
                  JOIN roomtype rt ON r.TypeID = rt.TypeID
                  WHERE r.TypeID = ? AND r.Status = 'Available'
                  AND NOT EXISTS (
                      SELECT 1 FROM booking b 
                      WHERE b.RoomNumber = r.RoomNumber 
                      AND (
                          (b.CheckInDate <= ? AND b.CheckOutDate > ?) OR
                          (b.CheckInDate < ? AND b.CheckOutDate >= ?)
                      )
                  )
                  LIMIT 1";
    
    $stmt = $conn->prepare($room_query);
    $stmt->bind_param("issss", $room_type, $checkin_date, $checkin_date, $checkout_date, $checkout_date);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$room) {
        throw new Exception("No available rooms of this type");
    }
    error_log("Found available room: " . $room['RoomNumber']);

    // 4. Validate capacity
    if ($guests <= 0 || $guests > $room['Capacity']) {
        throw new Exception("This room type accommodates {$room['Capacity']} guests maximum");
    }

    // 5. Validate dates
    $today = date('Y-m-d');
    if ($checkin_date < $today || $checkout_date <= $checkin_date) {
        throw new Exception("Invalid date selection");
    }

    // 6. Calculate price
    $date1 = new DateTime($checkin_date);
    $date2 = new DateTime($checkout_date);
    $num_nights = $date1->diff($date2)->days;
    $total_price = $room['PricePerNight'] * $num_nights;
    error_log("Total price calculated: $total_price");

    // 7. Update room status
    $update_room = $conn->prepare("UPDATE rooms SET Status = 'Booked' WHERE RoomNumber = ?");
    $update_room->bind_param("i", $room['RoomNumber']);
    $update_room->execute();
    
    if ($update_room->affected_rows !== 1) {
        throw new Exception("Failed to update room status");
    }
    $update_room->close();
    error_log("Room status updated to Booked");

    // 8. Insert booking record
    $insert_booking = $conn->prepare("INSERT INTO booking 
        (GuestID, RoomNumber, CheckInDate, CheckOutDate, TotalPrice, Capacity, Status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $insert_booking->bind_param("iissdi", 
        $guest_id, 
        $room['RoomNumber'],
        $checkin_date, 
        $checkout_date, 
        $total_price, 
        $guests);
    
    if (!$insert_booking->execute()) {
        throw new Exception("Failed to create booking: " . $conn->error);
    }
    
    $booking_id = $insert_booking->insert_id;
    $insert_booking->close();
    error_log("Booking record created with ID: $booking_id");

    // 9. Commit transaction
    $conn->commit();
    error_log("Transaction committed successfully");

    // 10. Redirect to booking processing page
    header("Location: booking_processing.php?booking_id=$booking_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Booking error: " . $e->getMessage());
    $_SESSION['booking_error'] = $e->getMessage();
    header("Location: booking.php");
    exit();
}

$conn->close();
?>