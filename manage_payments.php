<?php
session_start();
// Restrict access to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); // Last day of current month

// Get total revenue for the selected period
$revenue_query = "SELECT SUM(Amount) as total_revenue FROM payment WHERE PaymentDate BETWEEN ? AND ?";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$revenue_result = $stmt->get_result();
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;

// Get payment method distribution
$payment_methods_query = "SELECT PaymentMethod, COUNT(*) as count, SUM(Amount) as total 
                         FROM payment 
                         WHERE PaymentDate BETWEEN ? AND ?
                         GROUP BY PaymentMethod";
$stmt = $conn->prepare($payment_methods_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_methods_result = $stmt->get_result();
$payment_methods = [];
while ($row = $payment_methods_result->fetch_assoc()) {
    $payment_methods[] = $row;
}

// Get daily revenue for the chart
$daily_revenue_query = "SELECT DATE(PaymentDate) as date, SUM(Amount) as daily_revenue 
                       FROM payment 
                       WHERE PaymentDate BETWEEN ? AND ?
                       GROUP BY DATE(PaymentDate)
                       ORDER BY date";
$stmt = $conn->prepare($daily_revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_revenue_result = $stmt->get_result();
$daily_revenue = [];
while ($row = $daily_revenue_result->fetch_assoc()) {
    $daily_revenue[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLodge - Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            text-decoration: none;
        }
        
        /* Card Styles */
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
        
        /* Filter Styles */
        .filter-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .filter-label {
            font-weight: bold;
            color: var(--primary);
        }
        
        .filter-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .filter-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        /* Table Styles */
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
        
        .status-completed {
            color: var(--success);
            font-weight: bold;
        }
        
        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }
        
        .status-failed {
            color: var(--danger);
            font-weight: bold;
        }
        
        .payment-method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .method-credit {
            background: #e8f4f8;
            color: #2980b9;
        }
        
        .method-debit {
            background: #e8f6f3;
            color: #27ae60;
        }
        
        .method-cash {
            background: #fef9e7;
            color: #f39c12;
        }
        
        .method-paypal {
            background: #f4ecf7;
            color: #8e44ad;
        }
        
        .no-results {
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
                <a href="view_guests.php" class="menu-item">
                    <i class="fas fa-users"></i> View Guests
                </a>
                <a href="manage_rooms.php" class="menu-item">
                    <i class="fas fa-bed"></i> Manage Rooms
                </a>
                <a href="manage_payments.php" class="menu-item active">
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
                <h1 class="welcome-message">Payment Management</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Revenue Stats -->
            <div class="card-container">
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-rupee-sign"></i> Total Revenue
                    </div>
                    <div class="card-value">
                        ₹<?php echo number_format($total_revenue, 2); ?>
                    </div>
                    <div style="margin-top: 10px; color: #666;">
                        <?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-credit-card"></i> Payment Methods
                    </div>
                    <div class="card-value">
                        <?php echo count($payment_methods); ?>
                    </div>
                    <div style="margin-top: 10px; color: #666;">
                        Different payment methods used
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-receipt"></i> Total Transactions
                    </div>
                    <div class="card-value">
                        <?php 
                        $total_transactions = 0;
                        foreach ($payment_methods as $method) {
                            $total_transactions += $method['count'];
                        }
                        echo $total_transactions;
                        ?>
                    </div>
                    <div style="margin-top: 10px; color: #666;">
                        Completed payments
                    </div>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="filter-container">
                <div class="filter-label">Date Range:</div>
                <form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" name="start_date" class="filter-input" value="<?php echo $start_date; ?>">
                    <span>to</span>
                    <input type="date" name="end_date" class="filter-input" value="<?php echo $end_date; ?>">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </form>
            </div>

            <!-- Revenue Chart -->
            <div class="chart-container">
                <h2 class="chart-title">Daily Revenue</h2>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Payment Methods Chart -->
            <div class="chart-container">
                <h2 class="chart-title">Payment Methods Distribution</h2>
                <canvas id="paymentMethodsChart"></canvas>
            </div>

            <!-- Recent Payments Table -->
            <h2 style="margin: 20px 0;"><i class="fas fa-history"></i> Recent Payments</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Booking ID</th>
                            <th>Guest Name</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get recent payments with guest information
                        $payments_query = "SELECT p.*, b.BookingID, g.FullName 
                                         FROM payment p
                                         LEFT JOIN booking b ON p.BookingID = b.BookingID
                                         LEFT JOIN guest g ON b.GuestID = g.GuestID
                                         WHERE p.PaymentDate BETWEEN ? AND ?
                                         ORDER BY p.PaymentDate DESC
                                         LIMIT 10";
                        $stmt = $conn->prepare($payments_query);
                        $stmt->bind_param("ss", $start_date, $end_date);
                        $stmt->execute();
                        $payments_result = $stmt->get_result();
                        
                        if ($payments_result->num_rows > 0) {
                            while ($payment = $payments_result->fetch_assoc()):
                                // Determine payment method class
                                $method_class = '';
                                switch (strtolower($payment['PaymentMethod'])) {
                                    case 'credit card':
                                        $method_class = 'method-credit';
                                        break;
                                    case 'debit card':
                                        $method_class = 'method-debit';
                                        break;
                                    case 'cash':
                                        $method_class = 'method-cash';
                                        break;
                                    case 'paypal':
                                        $method_class = 'method-paypal';
                                        break;
                                    default:
                                        $method_class = '';
                                }
                        ?>
                        <tr>
                            <td><?php echo $payment['PaymentID']; ?></td>
                            <td><?php echo $payment['BookingID']; ?></td>
                            <td><?php echo htmlspecialchars($payment['FullName'] ?? 'Unknown'); ?></td>
                            <td>₹<?php echo number_format($payment['Amount'], 2); ?></td>
                            <td>
                                <span class="payment-method <?php echo $method_class; ?>">
                                    <?php echo htmlspecialchars($payment['PaymentMethod']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($payment['PaymentDate'])); ?></td>
                            <td>
                                <span class="status-badge status-success">Done</span>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="7" class="no-results">No payments found for the selected period</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode($daily_revenue); ?>;
        
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.date),
                datasets: [{
                    label: 'Daily Revenue',
                    data: revenueData.map(item => item.daily_revenue),
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `₹${context.raw.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
        
        // Payment Methods Chart
        const methodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
        const methodsData = <?php echo json_encode($payment_methods); ?>;
        
        const paymentMethodsChart = new Chart(methodsCtx, {
            type: 'doughnut',
            data: {
                labels: methodsData.map(item => item.PaymentMethod),
                datasets: [{
                    data: methodsData.map(item => item.total),
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(142, 68, 173, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(243, 156, 18, 1)',
                        'rgba(142, 68, 173, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = methodsData.reduce((sum, item) => sum + parseFloat(item.total), 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ₹${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 