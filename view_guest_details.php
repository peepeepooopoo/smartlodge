<?php
session_start();
// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Get guest ID from URL
$guest_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$guest_id) {
    header("Location: view_guests.php");
    exit();
}

// Get guest details
$guest_query = "SELECT * FROM guest WHERE GuestID = ?";
$stmt = $conn->prepare($guest_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$guest_result = $stmt->get_result();
$guest = $guest_result->fetch_assoc();

if (!$guest) {
    header("Location: view_guests.php");
    exit();
}

// Get guest's booking history
$bookings_query = "SELECT b.*, r.RoomType, r.Price 
                  FROM booking b 
                  LEFT JOIN rooms r ON b.RoomNumber = r.RoomNumber 
                  WHERE b.GuestID = ? 
                  ORDER BY b.CheckInDate DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLodge - Guest Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--primary);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            margin-top: 20px;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: block;
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid var(--secondary);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .welcome-message {
            font-size: 1.5rem;
        }
        
        .back-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        /* Guest Details Card */
        .guest-details {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .guest-details h2 {
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: var(--primary);
        }
        
        /* Bookings Table */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: var(--primary);
            color: white;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }
        
        .status-approved {
            color: var(--success);
            font-weight: bold;
        }
        
        .status-cancelled {
            color: var(--danger);
            font-weight: bold;
        }
        
        .no-bookings {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>SmartLodge</h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <a href="admin_dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_bookings.php" class="menu-item">
                    <i class="fas fa-calendar-check"></i> Manage Bookings
                </a>
                <a href="view_guests.php" class="menu-item active">
                    <i class="fas fa-users"></i> View Guests
                </a>
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i> Manage Rooms
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="welcome-message">Guest Details</h1>
                <div>
                    <a href="view_guests.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Guests
                    </a>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Guest Details Card -->
            <div class="guest-details">
                <h2><i class="fas fa-user"></i> Guest Information</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Guest ID</div>
                        <div class="detail-value"><?php echo $guest['GuestID']; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($guest['FullName']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($guest['Email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($guest['Phone']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?php echo htmlspecialchars($guest['Address']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date of Birth</div>
                        <div class="detail-value"><?php echo date('M d, Y', strtotime($guest['DateOfBirth'])); ?></div>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <h2 style="margin: 20px 0;"><i class="fas fa-history"></i> Booking History</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($bookings_result->num_rows > 0) {
                            while ($booking = $bookings_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $booking['BookingID']; ?></td>
                            <td><?php echo htmlspecialchars($booking['RoomNumber']); ?></td>
                            <td><?php echo htmlspecialchars($booking['RoomType']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['CheckInDate'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['CheckOutDate'])); ?></td>
                            <td>$<?php echo number_format($booking['TotalPrice'], 2); ?></td>
                            <td class="status-<?php echo strtolower($booking['Status']); ?>">
                                <?php echo ucfirst($booking['Status']); ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="7" class="no-bookings">No booking history found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 