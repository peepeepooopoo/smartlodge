<?php
session_start();
// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_number'], $_POST['status'])) {
    $room_number = (int)$_POST['room_number'];
    $status = $_POST['status'] === 'available' ? 'Available' : 'Booked';
    
    $stmt = $conn->prepare("UPDATE rooms SET Status = ? WHERE RoomNumber = ?");
    $stmt->bind_param("si", $status, $room_number);
    
    if ($stmt->execute()) {
        $_SESSION['room_message'] = "Room $room_number status updated to $status";
    } else {
        $_SESSION['room_error'] = "Error updating room status: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: manage_rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLodge - Manage Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reuse your existing admin dashboard styles */
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
        
        /* Room Management Styles */
        .room-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: var(--light);
        }
        
        .filter-btn.active {
            background: var(--secondary);
            color: white;
        }
        
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
        
        .status-available {
            color: var(--success);
            font-weight: bold;
        }
        
        .status-booked {
            color: var(--danger);
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
        
        .toggle-btn {
            background: var(--secondary);
            color: white;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
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
                <a href="view_guests.php" class="menu-item">
                    <i class="fas fa-users"></i> View Guests
                </a>
                <a href="manage_rooms.php" class="menu-item active">
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
                <h1 class="welcome-message">Manage Rooms</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Status Messages -->
            <?php if (isset($_SESSION['room_message'])): ?>
                <div class="message success-message">
                    <?php echo $_SESSION['room_message']; unset($_SESSION['room_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['room_error'])): ?>
                <div class="message error-message">
                    <?php echo $_SESSION['room_error']; unset($_SESSION['room_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Room Filters -->
            <div class="room-filters">
                <button class="filter-btn active" data-filter="all">All Rooms</button>
                <button class="filter-btn" data-filter="available">Available</button>
                <button class="filter-btn" data-filter="booked">Booked</button>
            </div>

            <!-- Rooms Table -->
            <div class="table-container">
                <table id="rooms-table">
                    <thead>
                        <tr>
                            <th>Room #</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Price/Night</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT 
                                    r.RoomNumber, 
                                    r.Status, 
                                    rt.Name as TypeName, 
                                    rt.PricePerNight, 
                                    rt.Capacity
                                FROM rooms r
                                JOIN roomtype rt ON r.TypeID = rt.TypeID
                                ORDER BY r.RoomNumber";
                        $result = $conn->query($query);
                        
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr data-status="<?php echo strtolower($row['Status']); ?>">
                            <td><?php echo $row['RoomNumber']; ?></td>
                            <td><?php echo htmlspecialchars($row['TypeName']); ?></td>
                            <td class="status-<?php echo strtolower($row['Status']); ?>">
                                <?php echo $row['Status']; ?>
                            </td>
                            <td>$<?php echo number_format($row['PricePerNight'], 2); ?></td>
                            <td><?php echo $row['Capacity']; ?></td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="room_number" value="<?php echo $row['RoomNumber']; ?>">
                                    <input type="hidden" name="status" value="<?php 
                                        echo strtolower($row['Status']) === 'available' ? 'booked' : 'available'; 
                                    ?>">
                                    <button type="submit" class="action-btn toggle-btn">
                                        Mark as <?php echo strtolower($row['Status']) === 'available' ? 'Booked' : 'Available'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="6">No rooms found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Filter rooms by status
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Filter rows
                const rows = document.querySelectorAll('#rooms-table tbody tr');
                rows.forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>