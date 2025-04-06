<?php
session_start();
include 'db_connection.php';
include 'auth_helpers.php';

// Use the require_admin_auth function to check admin access
require_admin_auth();

// Handle booking approval
if (isset($_POST['approve_booking'])) {
    $bookingId = $_POST['booking_id'];
    $roomNumber = $_POST['room_number'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE hotel_db.booking SET Status = 'approved' WHERE BookingID = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    
    // Update room status to 'booked'
    $stmt = $conn->prepare("UPDATE hotel_db.rooms SET Status = 'booked' WHERE RoomNumber = ?");
    $stmt->bind_param("s", $roomNumber);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Booking #$bookingId has been approved!";
    header("Location: manage_bookings.php");
    exit();
}

// Handle booking rejection
if (isset($_POST['reject_booking'])) {
    $bookingId = $_POST['booking_id'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE hotel_db.booking SET Status = 'cancelled' WHERE BookingID = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Booking #$bookingId has been rejected.";
    header("Location: manage_bookings.php");
    exit();
}

// Handle check-out
if (isset($_POST['check_out'])) {
    $bookingId = $_POST['booking_id'];
    $roomNumber = $_POST['room_number'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE hotel_db.booking SET Status = 'completed' WHERE BookingID = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    
    // Update room status to 'available'
    $stmt = $conn->prepare("UPDATE hotel_db.rooms SET Status = 'available' WHERE RoomNumber = ?");
    $stmt->bind_param("s", $roomNumber);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Booking #$bookingId completed and room $roomNumber marked as available!";
    header("Location: manage_bookings.php");
    exit();
}

// Handle room status change directly
if (isset($_POST['change_room_status'])) {
    $roomNumber = $_POST['room_number'];
    $newStatus = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE hotel_db.rooms SET Status = ? WHERE RoomNumber = ?");
    $stmt->bind_param("ss", $newStatus, $roomNumber);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Room $roomNumber status updated to $newStatus!";
    } else {
        $_SESSION['message'] = "Error updating room status: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: manage_bookings.php");
    exit();
}

// Handle status change
if (isset($_POST['new_status'])) {
    $bookingId = $_POST['booking_id'];
    $roomNumber = $_POST['room_number'];
    $newStatus = $_POST['new_status'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE hotel_db.booking SET Status = ? WHERE BookingID = ?");
    $stmt->bind_param("si", $newStatus, $bookingId);
    $stmt->execute();
    $stmt->close();
    
    // Update room status based on booking status
    if ($newStatus == 'approved') {
        $roomStatus = 'booked';
    } elseif ($newStatus == 'completed') {
        $roomStatus = 'available';
    } else {
        $roomStatus = 'available';
    }
    
    $stmt = $conn->prepare("UPDATE hotel_db.rooms SET Status = ? WHERE RoomNumber = ?");
    $stmt->bind_param("ss", $roomStatus, $roomNumber);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "Booking #$bookingId status updated to " . ucfirst($newStatus);
    header("Location: manage_bookings.php");
    exit();
}

// Get bookings with filters
$where = [];
$params = [];
$types = '';

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where[] = "b.Status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

$query = "SELECT b.*, g.FullName as guest_name, rt.Name as room_type 
          FROM booking b
          LEFT JOIN guest g ON b.GuestID = g.GuestID
          LEFT JOIN rooms r ON b.RoomNumber = r.RoomNumber
          LEFT JOIN roomtype rt ON r.TypeID = rt.TypeID
          $whereClause
          ORDER BY b.BookingID DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - SmartLodge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Variables */
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        /* Admin Container */
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

        .sidebar-header h2 {
            color: white;
            margin-bottom: 5px;
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
            color: var(--primary);
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary);
        }

        .form-group select, 
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .filter-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-btn:hover {
            background: #1a252f;
        }

        /* Messages */
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #34495e;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Classes */
        .status-pending {
            color: var(--warning);
            font-weight: bold;
        }

        .status-approved {
            color: var(--success);
            font-weight: bold;
        }

        .status-completed {
            color: var(--secondary);
            font-weight: bold;
        }

        .status-cancelled {
            color: var(--danger);
            font-weight: bold;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }

        .action-btn i {
            margin-right: 5px;
        }

        .approve-btn {
            background: var(--success);
            color: white;
        }

        .approve-btn:hover {
            background: #27ae60;
        }

        .check-out-btn {
            background: var(--secondary);
            color: white;
        }

        .check-out-btn:hover {
            background: #2980b9;
        }

        .view-btn {
            background: #6c757d;
            color: white;
        }

        .view-btn:hover {
            background: #5a6268;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .logout-btn {
                margin-top: 10px;
            }
        }
       
        /* [Previous CSS remains the same, add this new style] */
        .status-select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }
        
        .status-available { background-color: #d4edda; }
        .status-booked { background-color: #fff3cd; }
        .status-occupied { background-color: #f8d7da; }
        
        .update-status-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-left: 5px;
        }

        /* Status Colors with improved visibility */
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .status-completed {
            background-color: #cce5ff;
            color: #004085;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        /* Action Buttons with improved visibility */
        .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .action-btn {
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 35px;
            height: 35px;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .check-out-btn {
            background-color: #17a2b8;
            color: white;
        }

        .check-out-btn:hover {
            background-color: #138496;
        }

        .view-btn {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .view-btn:hover {
            background-color: #5a6268;
        }

        /* Filter Section with improved visibility */
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
        }

        .filter-btn {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 600;
        }

        .filter-btn:hover {
            background-color: #34495e;
        }

        /* Add these new styles */
        .status-select {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            min-width: 120px;
            text-align: center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 16px;
        }

        .status-select.status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-select.status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-select.status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-select.status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
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
                <a href="manage_bookings.php" class="menu-item active">
                    <i class="fas fa-calendar-check"></i> Manage Bookings
                </a>
                <a href="view_guests.php" class="menu-item">
                    <i class="fas fa-users"></i> View Guests
                </a>
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i> Manage Rooms
                </a>
                <a href="manage_payments.php" class="menu-item">
                    <i class="fas fa-bed"></i> Payments
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="welcome-message">Manage Bookings</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success-message">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <form class="filter-form" method="GET">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Bookings Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest Name</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['BookingID']; ?></td>
                            <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                            <td><?php echo $row['RoomNumber']; ?></td>
                            <td><?php echo htmlspecialchars($row['room_type']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['CheckInDate'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['CheckOutDate'])); ?></td>
                            <td>$<?php echo number_format($row['TotalPrice'], 2); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['BookingID']; ?>">
                                    <input type="hidden" name="room_number" value="<?php echo $row['RoomNumber']; ?>">
                                    <select name="new_status" class="status-select status-<?php echo strtolower($row['Status']); ?>" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $row['Status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $row['Status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $row['Status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="completed" <?php echo $row['Status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add confirmation before status change
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function(e) {
                if (!confirm('Are you sure you want to change the booking status?')) {
                    e.preventDefault();
                    this.value = this.getAttribute('data-original-value');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>