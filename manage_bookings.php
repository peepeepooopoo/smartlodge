<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

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
    
    $_SESSION['message'] = "Booking #$bookingId approved and room $roomNumber marked as booked!";
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
            position: sticky;
            top: 0;
            font-weight: 500;
        }

        tr:hover {
            background: #f9f9f9;
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
                        <label for="room_number">Room Number</label>
                        <input type="text" id="room_number" name="room_number" placeholder="Enter room number" value="<?php echo isset($_GET['room_number']) ? htmlspecialchars($_GET['room_number']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="guest_id">Guest ID</label>
                        <input type="text" id="guest_id" name="guest_id" placeholder="Enter guest ID" value="<?php echo isset($_GET['guest_id']) ? htmlspecialchars($_GET['guest_id']) : ''; ?>">
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
                <th>Guest</th>
                <th>Room Number</th>
                <th>Room Status</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Booking Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Build filter query
            $where = [];
            $params = [];
            $types = '';
            
            if (!empty($_GET['status'])) {
                $where[] = "b.Status = ?";
                $params[] = $_GET['status'];
                $types .= 's';
            }
            
            if (!empty($_GET['room_number'])) {
                $where[] = "b.RoomNumber = ?";
                $params[] = $_GET['room_number'];
                $types .= 's';
            }
            
            if (!empty($_GET['guest_id'])) {
                $where[] = "b.GuestID = ?";
                $params[] = $_GET['guest_id'];
                $types .= 'i';
            }
            
            $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
            
            $query = "SELECT 
                        b.BookingID, 
                        b.GuestID, 
                        b.RoomNumber, 
                        b.CheckInDate, 
                        b.CheckOutDate, 
                        b.TotalPrice, 
                        b.Status as booking_status,
                        u.name as guest_name,
                        r.Status as room_status
                    FROM hotel_db.booking b
                    LEFT JOIN hotel_db.users u ON b.GuestID = u.id
                    LEFT JOIN hotel_db.rooms r ON b.RoomNumber = r.RoomNumber
                    $whereClause
                    ORDER BY b.CheckInDate DESC";
                    
            $stmt = $conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $row['BookingID']; ?></td>
                <td>
                    <?php 
                    if (!empty($row['guest_name'])) {
                        echo htmlspecialchars($row['guest_name']) . " (ID: " . $row['GuestID'] . ")";
                    } else {
                        echo $row['GuestID'];
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['RoomNumber']); ?></td>
                <td class="status-<?php echo strtolower($row['room_status']); ?>">
                    <form method="POST" style="display: inline-flex; align-items: center;">
                        <input type="hidden" name="room_number" value="<?php echo $row['RoomNumber']; ?>">
                        <select name="new_status" class="status-select">
                            <option value="available" <?php echo $row['room_status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="booked" <?php echo $row['room_status'] == 'booked' ? 'selected' : ''; ?>>Booked</option>
                            <option value="occupied" <?php echo $row['room_status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                        </select>
                        <button type="submit" name="change_room_status" class="update-status-btn">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                    </form>
                </td>
                <td><?php echo date('M d, Y', strtotime($row['CheckInDate'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['CheckOutDate'])); ?></td>
                <td class="status-<?php echo strtolower($row['booking_status']); ?>">
                    <?php echo ucfirst($row['booking_status']); ?>
                </td>
                <td>
                    <?php if ($row['booking_status'] == 'pending'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $row['BookingID']; ?>">
                            <input type="hidden" name="room_number" value="<?php echo $row['RoomNumber']; ?>">
                            <button type="submit" name="approve_booking" class="action-btn approve-btn">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                    <?php elseif ($row['booking_status'] == 'approved' && strtotime($row['CheckOutDate']) <= time()): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $row['BookingID']; ?>">
                            <input type="hidden" name="room_number" value="<?php echo $row['RoomNumber']; ?>">
                            <button type="submit" name="check_out" class="action-btn check-out-btn">
                                <i class="fas fa-door-open"></i> Check Out
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="view_booking.php?id=<?php echo $row['BookingID']; ?>" class="action-btn view-btn">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            <?php 
                endwhile;
            } else {
                echo '<tr><td colspan="8" style="text-align: center;">No bookings found matching your criteria</td></tr>';
            }
            
            $stmt->close();
            $conn->close();
            ?>
        </tbody>
    </table>
            </div>
        </div>
    </div>
</body>
</html>