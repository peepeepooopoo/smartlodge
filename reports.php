<?php
session_start();
include 'db_connection.php';
include 'auth_helpers.php';

// Use the require_admin_auth function to check admin access
require_admin_auth();

// Get time period filter
$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Set date range based on period
switch($period) {
    case 'daily':
        $start_date = $date;
        $end_date = $date;
        $group_by = "DATE(p.PaymentDate)";
        break;
    case 'weekly':
        $start_date = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
        $group_by = "YEARWEEK(p.PaymentDate)";
        break;
    case 'monthly':
        $start_date = date('Y-m-01', strtotime($date));
        $end_date = date('Y-m-t', strtotime($date));
        $group_by = "DATE_FORMAT(p.PaymentDate, '%Y-%m')";
        break;
    case 'yearly':
        $start_date = date('Y-01-01', strtotime($date));
        $end_date = date('Y-12-31', strtotime($date));
        $group_by = "YEAR(p.PaymentDate)";
        break;
}

// Get financial summary
$financial_query = "SELECT 
    COUNT(DISTINCT b.BookingID) as total_bookings,
    COUNT(DISTINCT b.GuestID) as total_guests,
    COUNT(DISTINCT b.RoomNumber) as total_rooms_booked,
    SUM(p.Amount) as total_revenue,
    AVG(p.Amount) as average_booking_value
    FROM booking b
    JOIN payment p ON b.BookingID = p.BookingID
    WHERE p.PaymentDate BETWEEN ? AND ?";

$stmt = $conn->prepare($financial_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$financial_summary = $stmt->get_result()->fetch_assoc();

// Get room type distribution
$room_type_query = "SELECT 
    rt.Name as room_type,
    COUNT(*) as booking_count,
    SUM(p.Amount) as revenue
    FROM booking b
    JOIN rooms r ON b.RoomNumber = r.RoomNumber
    JOIN roomtype rt ON r.TypeID = rt.TypeID
    JOIN payment p ON b.BookingID = p.PaymentID
    WHERE p.PaymentDate BETWEEN ? AND ?
    GROUP BY rt.Name
    ORDER BY booking_count DESC";

$stmt = $conn->prepare($room_type_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$room_type_stats = $stmt->get_result();

// Get daily/weekly/monthly/yearly trends
$trends_query = "SELECT 
    $group_by as period,
    COUNT(DISTINCT b.BookingID) as bookings,
    SUM(p.Amount) as revenue,
    COUNT(DISTINCT b.GuestID) as guests
    FROM booking b
    JOIN payment p ON b.BookingID = p.PaymentID
    WHERE p.PaymentDate BETWEEN ? AND ?
    GROUP BY period
    ORDER BY period";

$stmt = $conn->prepare($trends_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$trends = $stmt->get_result();

// Get payment method distribution
$payment_method_query = "SELECT 
    PaymentMethod,
    COUNT(*) as count,
    SUM(Amount) as total_amount
    FROM payment
    WHERE PaymentDate BETWEEN ? AND ?
    GROUP BY PaymentMethod";

$stmt = $conn->prepare($payment_method_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_methods = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SmartLodge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
        }

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

        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

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

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            color: var(--primary);
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary);
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary);
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-success { background-color: var(--success); color: white; }
        .status-warning { background-color: var(--warning); color: black; }
        .status-danger { background-color: var(--danger); color: white; }
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
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i> Manage Rooms
                </a>
                <a href="manage_payments.php" class="menu-item">
                    <i class="fas fa-money-bill"></i> Payments
                </a>
                <a href="reports.php" class="menu-item active">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Reports & Analytics</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form class="filter-form" method="GET">
                    <div class="form-group">
                        <label for="period">Time Period</label>
                        <select name="period" id="period" onchange="this.form.submit()">
                            <option value="daily" <?php echo $period == 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $period == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo $period == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="yearly" <?php echo $period == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Select Date</label>
                        <input type="date" name="date" id="date" value="<?php echo $date; ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- Summary Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <div class="stat-value"><?php echo number_format($financial_summary['total_bookings']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="stat-value">₹<?php echo number_format($financial_summary['total_revenue'], 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Guests</h3>
                    <div class="stat-value"><?php echo number_format($financial_summary['total_guests']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Average Booking Value</h3>
                    <div class="stat-value">₹<?php echo number_format($financial_summary['average_booking_value'], 2); ?></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="chart-grid">
                <div class="chart-container">
                    <h3>Revenue Trends</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Room Type Distribution</h3>
                    <canvas id="roomTypeChart"></canvas>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="chart-container">
                <h3>Payment Method Distribution</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Number of Transactions</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($method = $payment_methods->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($method['PaymentMethod']); ?></td>
                            <td><?php echo number_format($method['count']); ?></td>
                            <td>₹<?php echo number_format($method['total_amount'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Revenue Trends Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php 
                    $labels = [];
                    $revenue = [];
                    while ($row = $trends->fetch_assoc()) {
                        $labels[] = $row['period'];
                        $revenue[] = $row['revenue'];
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($revenue); ?>,
                    borderColor: '#3498db',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Room Type Distribution Chart
        const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
        new Chart(roomTypeCtx, {
            type: 'pie',
            data: {
                labels: <?php 
                    $labels = [];
                    $data = [];
                    while ($row = $room_type_stats->fetch_assoc()) {
                        $labels[] = $row['room_type'];
                        $data[] = $row['booking_count'];
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: [
                        '#2ecc71',
                        '#3498db',
                        '#f1c40f',
                        '#e74c3c',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html> 