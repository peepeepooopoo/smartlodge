<?php
session_start();
include 'db_connection.php';

// Check if user is logged in as a guest
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch guest information
$guest_id = $_SESSION['guest_id'];
$guest_query = "SELECT * FROM guest WHERE GuestID = ?";
$stmt = $conn->prepare($guest_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
$guest = $result->fetch_assoc();
$stmt->close();

// Fetch guest's bookings
$bookings_query = "SELECT b.*, r.RoomNumber, rt.Name as RoomType, rt.PricePerNight 
                  FROM booking b 
                  JOIN rooms r ON b.RoomNumber = r.RoomNumber 
                  JOIN roomtype rt ON r.TypeID = rt.TypeID 
                  WHERE b.GuestID = ? 
                  ORDER BY b.CheckInDate DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = [];
while ($row = $bookings_result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Fetch guest's payments
$payments_query = "SELECT p.*, b.BookingID 
                  FROM payment p 
                  JOIN booking b ON p.BookingID = b.BookingID 
                  WHERE b.GuestID = ? 
                  ORDER BY p.PaymentDate DESC";
$stmt = $conn->prepare($payments_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$payments_result = $stmt->get_result();
$payments = [];
while ($row = $payments_result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Profile - SmartLodge</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poiret+One&display=swap');

        body {
            font-family: Arial, sans-serif;
            background: url("register1.jpg");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            position: relative;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
            z-index: -1;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-left {
            display: flex;
            gap: 20px;
            margin-left: 20px;
        }

        .navbar-left a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .navbar-left a:hover {
            color: #d9534f;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-right: 20px;
        }

        .welcome-text {
            color: white;
            font-size: 16px;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-button {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .logout-button:hover {
            color: #d9534f;
        }

        .Sign-in, .Login {
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .Sign-in a, .Login a {
            text-decoration: none;
            color: white;
            font-size: 16px;
        }

        .Sign-in {
            background-color: #d9534f;
        }

        .Sign-in:hover {
            background-color: #c9302c;
        }

        .Login {
            background-color: transparent;
            border: 1px solid white;
        }

        .Login:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .profile-container {
            max-width: 1000px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .profile-header {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #d9534f;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h1 {
            font-family: "Poiret One", sans-serif;
            color: #333;
            margin: 0 0 10px 0;
            font-size: 2.5rem;
        }

        .profile-info p {
            margin: 5px 0;
            color: #555;
            font-size: 1.1rem;
        }

        .profile-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #d9534f;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #c9302c;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .section-title {
            font-family: "Poiret One", sans-serif;
            color: white;
            margin: 30px 0 20px;
            font-size: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.3);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .card-title {
            font-family: "Poiret One", sans-serif;
            color: #333;
            margin: 0;
            font-size: 1.5rem;
        }

        .card-body {
            color: #555;
        }

        .booking-item, .payment-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .booking-item:last-child, .payment-item:last-child {
            border-bottom: none;
        }

        .booking-header, .payment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .booking-details, .payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .detail-item {
            margin: 5px 0;
        }

        .detail-label {
            font-weight: bold;
            color: #333;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-confirmed {
            background-color: #28a745;
            color: white;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }

        .status-completed {
            background-color: #17a2b8;
            color: white;
        }

        .empty-message {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-actions {
                justify-content: center;
                margin-top: 20px;
            }

            .booking-details, .payment-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-left">
            <div><a href="index.php">Home</a></div>
            <div><a href="#about">About</a></div>
            <div><a href="booking.php">Bookings</a></div>
            <div><a href="rooms.php">Rooms</a></div>
            <div><a href="#contact">Contact</a></div>
        </div>
        <div class="navbar-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div id="profile-container" style="display: flex; align-items: center; gap: 10px;">
                    <?php if (isset($_SESSION['name'])): ?>
                        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <?php elseif (isset($_SESSION['email'])): ?>
                        <span class="welcome-text">Welcome, <?php echo htmlspecialchars(explode('@', $_SESSION['email'])[0]); ?></span>
                    <?php else: ?>
                        <span class="welcome-text">Welcome, User</span>
                    <?php endif; ?>
                    
                    <a class="profile-btn" href="guest_profile.php">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="#d9534f" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke="#d9534f" stroke-width="2" fill="none"></path>
                        </svg>
                    </a>
                    <a href="logout.php" class="logout-button">Logout</a>
                </div>
            <?php else: ?>
                <div id="signup-btn" class="Sign-in"><a href="register.php">Sign Up</a></div>
                <div id="login-btn" class="Login"><a href="login.php">Login</a></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($guest['FullName'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($guest['FullName']); ?></h1>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($guest['Email']); ?></p>
                <?php if (isset($guest['RegistrationDate']) && !empty($guest['RegistrationDate'])): ?>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($guest['RegistrationDate'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="profile-actions">
                <a href="booking.php" class="btn btn-primary">Book a Room</a>
                <a href="edit_profile.php" class="btn btn-secondary">Edit Profile</a>
            </div>
        </div>

        <h2 class="section-title">My Bookings</h2>
        <?php if (empty($bookings)): ?>
            <div class="card">
                <div class="empty-message">You haven't made any bookings yet.</div>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="card">
                    <div class="booking-item">
                        <div class="booking-header">
                            <h3 class="card-title">Booking #<?php echo $booking['BookingID']; ?></h3>
                            <span class="status status-<?php echo strtolower($booking['Status']); ?>">
                                <?php echo $booking['Status']; ?>
                            </span>
                        </div>
                        <div class="booking-details">
                            <div class="detail-item">
                                <span class="detail-label">Room:</span> 
                                <?php echo $booking['RoomNumber']; ?> (<?php echo $booking['RoomType']; ?>)
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Check-in:</span> 
                                <?php echo date('F j, Y', strtotime($booking['CheckInDate'])); ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Check-out:</span> 
                                <?php echo date('F j, Y', strtotime($booking['CheckOutDate'])); ?>
                            </div>
                            <?php if (isset($booking['NumberOfGuests']) && !empty($booking['NumberOfGuests'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Guests:</span> 
                                <?php echo $booking['NumberOfGuests']; ?>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <span class="detail-label">Price:</span> 
                                $<?php echo number_format($booking['PricePerNight'], 2); ?> per night
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total:</span> 
                                $<?php echo number_format($booking['TotalPrice'], 2); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2 class="section-title">Payment History</h2>
        <?php if (empty($payments)): ?>
            <div class="card">
                <div class="empty-message">No payment history available.</div>
            </div>
        <?php else: ?>
            <?php foreach ($payments as $payment): ?>
                <div class="card">
                    <div class="payment-item">
                        <div class="payment-header">
                            <h3 class="card-title">Payment #<?php echo $payment['PaymentID']; ?></h3>
                            <span class="status status-confirmed">Paid</span>
                        </div>
                        <div class="payment-details">
                            <div class="detail-item">
                                <span class="detail-label">Booking ID:</span> 
                                <?php echo $payment['BookingID']; ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date:</span> 
                                <?php echo date('F j, Y', strtotime($payment['PaymentDate'])); ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Amount:</span> 
                                $<?php echo number_format($payment['Amount'], 2); ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Method:</span> 
                                <?php echo $payment['PaymentMethod']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?> 