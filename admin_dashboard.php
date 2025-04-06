<?php
session_start();
include 'db_connection.php';
include 'auth_helpers.php';

// Use the require_admin_auth function to check admin access
require_admin_auth();

// Get admin name from session
$admin_name = get_admin_name();
$admin_email = $_SESSION['admin_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLodge - Admin Dashboard</title>
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
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .card-title i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
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
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 0.8rem;
        }
        
        .approve-btn {
            background: var(--success);
            color: white;
        }
        
        .reject-btn {
            background: var(--danger);
            color: white;
        }
        
        .view-btn {
            background: var(--secondary);
            color: white;
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
                <a href="admin_dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_bookings.php" class="menu-item">
                    <i class="fas fa-calendar-check"></i> Manage Bookings
                </a>
                <a href="view_guests.php" class="menu-item">
                    <i class="fas fa-users"></i> View Guests
                </a>
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i> Manage Rooms
                </a>
                <a href="manage_payments.php" class="menu-item">
                    <i class="fas fa-money-bill-wave"></i> Payments
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
                <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="card-container">
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-calendar"></i> Total Bookings
                    </div>
                    <div class="card-value">
                        <?php
                        $query = "SELECT COUNT(*) as total FROM booking";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-user-check"></i> Active Guests
                    </div>
                    <div class="card-value">
                        <?php
                         $query = "SELECT COUNT(DISTINCT GuestID) as total FROM booking WHERE CheckOutDate > NOW()";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-bed"></i> Available Rooms
                    </div>
                    <div class="card-value">
                        <?php
                        $query = "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-clock"></i> Pending Approvals
                    </div>
                    <div class="card-value">
                        <?php
                        $query = "SELECT COUNT(*) as total FROM booking WHERE status = 'pending'";
                        $result = $conn->query($query);
                        echo $result->fetch_assoc()['total'];
                        ?>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <h2 style="margin-bottom: 15px;"><i class="fas fa-history"></i> Recent Bookings</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest ID</th>
                            <th>Room Number</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT 
                                    b.BookingID, 
                                    b.GuestID, 
                                    b.RoomNumber, 
                                    b.CheckInDate, 
                                    b.CheckOutDate, 
                                    b.TotalPrice, 
                                    b.Status,
                                    u.name as guest_name
                                FROM booking b
                                LEFT JOIN users u ON b.GuestID = u.id
                                ORDER BY b.CheckInDate DESC LIMIT 5";
                        $result = $conn->query($query);
                        
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
                            <td><?php echo date('M d, Y', strtotime($row['CheckInDate'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['CheckOutDate'])); ?></td>
                            <td>$<?php echo number_format($row['TotalPrice'], 2); ?></td>
                            <td class="status-<?php echo strtolower($row['Status']); ?>">
                                <?php echo ucfirst($row['Status']); ?>
                            </td>
                            <td>
                                <?php if (strtolower($row['Status']) == 'pending'): ?>
                                    <button class="action-btn approve-btn">Approve</button>
                                    <button class="action-btn reject-btn">Reject</button>
                                <?php endif; ?>
                                <button class="action-btn view-btn">View</button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="8">No bookings found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // JavaScript for button actions
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.closest('tr').querySelector('td:first-child').textContent;
                if (confirm(`Approve booking #${bookingId}?`)) {
                    // AJAX call to update status
                    fetch('update_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${bookingId}&status=approved`
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        location.reload();
                    });
                }
            });
        });

        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.closest('tr').querySelector('td:first-child').textContent;
                if (confirm(`Reject booking #${bookingId}?`)) {
                    // AJAX call to update status
                    fetch('update_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${bookingId}&status=rejected`
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        location.reload();
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>